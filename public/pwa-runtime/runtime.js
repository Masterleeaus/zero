/**
 * TitanRuntime - Main PWA runtime coordinator for Titan Zero — v3
 *
 * Phase 3 improvements:
 *   - Device capability profiling (tier classification, feature detection)
 *   - Capability profile sent on handshake + stored locally
 *   - Rich runtime contract field name alignment (sync_policy, feature_flags etc)
 *   - Conflict event local queue
 *   - Reconnect-triggered deferred replay
 *   - Offline artifact staging helpers
 */
import db from './db.js';
import { TitanSignalQueue } from './signalQueue.js';
import { TitanSyncEngine } from './sync.js';

class TitanRuntime {
    constructor() {
        this.db = db;
        this.signalQueue = null;
        this.syncEngine = null;
        this.nodeId = null;
        this.deviceLabel = null;
        this.trustLevel = null;
        this._appVersion = document.documentElement.dataset.appVersion ?? '1.0.0';
        this._contract = null;
        this._capabilityProfile = null;
    }

    async init() {
        try {
            await this.db.open();

            this.nodeId = await this._loadOrGenerateNodeId();

            // Profile device capabilities and store locally
            this._capabilityProfile = await this._buildCapabilityProfile();

            // Load bootstrap contract from server (or cache)
            this._contract = await this._loadBootstrapContract();

            this.signalQueue = new TitanSignalQueue(this.db);
            this.syncEngine = new TitanSyncEngine(this.db, this.signalQueue);

            // Apply contract-derived settings to sync engine (Pass 3: new field names)
            if (this._contract) {
                const policy = this._contract.sync_policy ?? {};
                if (policy.sync_interval_ms) {
                    this.syncEngine.syncInterval = policy.sync_interval_ms;
                } else if (this._contract.syncInterval) {
                    // backward compat with pass 2 field names
                    this.syncEngine.syncInterval = this._contract.syncInterval;
                }
                if (policy.batch_limit) {
                    this.syncEngine._batchSize = Math.min(policy.batch_limit, this.syncEngine._maxBatch);
                } else if (this._contract.syncBatchLimit) {
                    this.syncEngine._batchSize = Math.min(this._contract.syncBatchLimit, this.syncEngine._maxBatch);
                }
            }

            if (navigator.onLine) {
                await this.handshake().catch((e) => {
                    console.warn('[TitanRuntime] Handshake failed (non-fatal):', e instanceof Error ? e.message : String(e));
                });
            }

            this.syncEngine.start();

            // Listen for SW messages
            if (navigator.serviceWorker) {
                navigator.serviceWorker.addEventListener('message', (event) => {
                    if (!event.data) return;
                    if (event.data.type === 'titan:sw-update-ready') {
                        window.dispatchEvent(new CustomEvent('titan:sw-update-ready'));
                    }
                    if (event.data.type === 'BG_SYNC_TRIGGERED' || event.data.type === 'CONNECTION_RESTORED') {
                        this._triggerReconnectReplay().catch(() => {});
                    }
                });
            }

            window.dispatchEvent(new CustomEvent('titan:runtime-ready', {
                detail: {
                    nodeId: this.nodeId,
                    trustLevel: this.trustLevel,
                    capabilityTier: this._capabilityProfile?.tier ?? null,
                },
            }));
        } catch (err) {
            console.error('[TitanRuntime] Init failed:', err);
        }
    }

    async handshake() {
        const csrfToken = this._getCsrfToken();
        const response = await fetch('/pwa/handshake', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: JSON.stringify({
                node_id: this.nodeId,
                platform: navigator.platform ?? 'unknown',
                app_version: this._appVersion,
                device_label: this.deviceLabel ?? navigator.userAgent,
                runtime_version: this._contract?.runtime_version ?? '3',
                capability_profile: this._capabilityProfile ?? {},
                capability_tier: this._capabilityProfile?.tier ?? null,
            }),
        });

        if (!response.ok) {
            throw new Error(`Handshake HTTP ${response.status}`);
        }

        const data = await response.json().catch(() => ({}));

        if (data.device_label) {
            this.deviceLabel = data.device_label;
        }
        if (data.trust_level) {
            this.trustLevel = data.trust_level;
            await this.db.put('runtime_meta', { key: 'trust_level', value: data.trust_level });
        }

        return data;
    }

    async emit(signalKey, payload, meta = {}) {
        if (!this.signalQueue) {
            throw new Error('TitanRuntime not initialized. Call init() first.');
        }
        return this.signalQueue.enqueue(signalKey, payload, meta);
    }

    /**
     * Stage an offline artifact (photo/note/proof) via server endpoint.
     * Only stores metadata — binary upload is handled separately when online.
     */
    async stageArtifact(artifactType, meta = {}, options = {}) {
        const clientRef = options.clientRef ?? `artifact-${Date.now()}-${Math.random().toString(36).slice(2)}`;

        // Always store locally first
        await this.db.put('staged_uploads', {
            artifact_type: artifactType,
            client_ref: clientRef,
            job_id: options.jobId ?? null,
            status: 'pending',
            meta,
            created_at: Date.now(),
        });

        // Try server staging if online
        if (navigator.onLine) {
            const csrfToken = this._getCsrfToken();
            try {
                const res = await fetch('/pwa/staging/artifacts', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                    body: JSON.stringify({
                        node_id: this.nodeId,
                        artifacts: [{
                            artifact_type: artifactType,
                            client_ref: clientRef,
                            job_id: options.jobId ?? null,
                            meta,
                            ...options,
                        }],
                    }),
                });
                if (res.ok) {
                    return { client_ref: clientRef, staged: true };
                }
            } catch (e) {
                console.warn('[TitanRuntime] Server artifact staging failed (will retry):', e.message);
            }
        }

        return { client_ref: clientRef, staged: false, queued_offline: true };
    }

    /**
     * Record a conflict event locally for operator visibility.
     */
    async recordConflict(conflictType, data = {}) {
        await this.db.put('conflict_queue', {
            conflict_type: conflictType,
            data,
            resolved: false,
            created_at: Date.now(),
        });
    }

    getNodeId() {
        return this.nodeId;
    }

    getTrustLevel() {
        return this.trustLevel;
    }

    getContract() {
        return this._contract;
    }

    getCapabilityProfile() {
        return this._capabilityProfile;
    }

    async status() {
        const syncStatus = this.syncEngine
            ? await this.syncEngine.getStatus()
            : { isSyncing: false, lastSyncAt: null, pendingCount: 0, isOnline: navigator.onLine };

        const queueSize = this.signalQueue ? await this.signalQueue.getQueueSize() : 0;

        return {
            nodeId: this.nodeId,
            trustLevel: this.trustLevel,
            deviceLabel: this.deviceLabel,
            appVersion: this._appVersion,
            capabilityTier: this._capabilityProfile?.tier ?? null,
            ...syncStatus,
            queueSize,
        };
    }

    // ─── Private ───────────────────────────────────────────────────────────────

    async _loadBootstrapContract() {
        // Always fetch fresh on init if online, fall back to cache
        if (navigator.onLine) {
            try {
                const res = await fetch('/pwa/bootstrap', {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                });
                if (res.ok) {
                    const contract = await res.json();
                    await this.db.put('bootstrap_meta', { key: 'contract', value: contract });
                    return contract;
                }
            } catch (e) {
                console.warn('[TitanRuntime] Bootstrap fetch failed, using cache:', e.message);
            }
        }

        // Fallback: use cached contract
        try {
            const cached = await this.db.get('bootstrap_meta', 'contract');
            if (cached?.value) return cached.value;
        } catch (e) {
            // ignore
        }

        return null;
    }

    async _loadOrGenerateNodeId() {
        try {
            const existing = await this.db.get('runtime_meta', 'node_id');
            if (existing?.value) {
                return existing.value;
            }
        } catch (e) {
            // Fall through to generate
        }

        const nodeId = this._generateNodeId();
        await this.db.put('runtime_meta', { key: 'node_id', value: nodeId });
        return nodeId;
    }

    /**
     * Build a device capability profile and store it in IndexedDB.
     *
     * Tier classification:
     *   mobile_light     — mobile, low memory, no background sync
     *   mobile_standard  — mobile, background sync available
     *   tablet_standard  — tablet-sized, decent memory
     *   desktop_full     — desktop, full feature support
     */
    async _buildCapabilityProfile() {
        const ua = navigator.userAgent ?? '';
        const isMobile = /Mobi|Android|iPhone|iPod/i.test(ua);
        const isTablet = /iPad|Tablet/i.test(ua) || (!isMobile && /Android/i.test(ua));
        const isDesktop = !isMobile && !isTablet;

        const memory = navigator.deviceMemory ?? null;         // GB (hint only)
        const connection = navigator.connection ?? navigator.mozConnection ?? navigator.webkitConnection ?? null;
        const networkType = connection?.effectiveType ?? connection?.type ?? null;
        const downlink = connection?.downlink ?? null;

        const swSupport = 'serviceWorker' in navigator;
        const bgSyncSupport = swSupport && 'SyncManager' in window;
        const idbSupport = 'indexedDB' in window;
        const cameraSupport = !!(navigator.mediaDevices?.getUserMedia);
        const geoSupport = 'geolocation' in navigator;
        const storagePersistenceSupport = !!(navigator.storage?.persist);
        const notificationSupport = 'Notification' in window;
        const webLocksSupport = 'locks' in navigator;

        // Tier classification
        let tier = 'mobile_light';
        if (isDesktop) {
            tier = 'desktop_full';
        } else if (isTablet) {
            tier = 'tablet_standard';
        } else if (isMobile && bgSyncSupport && (memory === null || memory >= 2)) {
            tier = 'mobile_standard';
        }

        const profile = {
            tier,
            platform: navigator.platform ?? 'unknown',
            user_agent_hint: ua.slice(0, 120),
            is_mobile: isMobile,
            is_tablet: isTablet,
            is_desktop: isDesktop,
            memory_gb: memory,
            network_type: networkType,
            downlink_mbps: downlink,
            service_worker: swSupport,
            background_sync: bgSyncSupport,
            indexed_db: idbSupport,
            camera: cameraSupport,
            geolocation: geoSupport,
            storage_persistence: storagePersistenceSupport,
            notifications: notificationSupport,
            web_locks: webLocksSupport,
            screen_width: window.screen?.width ?? null,
            screen_height: window.screen?.height ?? null,
            device_pixel_ratio: window.devicePixelRatio ?? null,
            profiled_at: new Date().toISOString(),
        };

        // Persist locally
        await this.db.put('capability_profile', { key: 'current', value: profile });
        await this.db.put('runtime_meta', { key: 'capability_tier', value: tier });

        return profile;
    }

    /**
     * Trigger reconnect replay for deferred signals on this node.
     */
    async _triggerReconnectReplay() {
        if (!this.nodeId) return;
        const csrfToken = this._getCsrfToken();
        try {
            await fetch('/pwa/sync/reconnect', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify({ node_id: this.nodeId }),
            });
        } catch (e) {
            // non-fatal
        }
    }

    _generateNodeId() {
        const array = new Uint8Array(16);
        crypto.getRandomValues(array);
        array[6] = (array[6] & 0x0f) | 0x40;
        array[8] = (array[8] & 0x3f) | 0x80;
        const hex = Array.from(array).map((b) => b.toString(16).padStart(2, '0')).join('');
        return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    }

    _getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
    }
}

const instance = new TitanRuntime();

if (typeof window !== 'undefined') {
    window.TitanRuntime = instance;
}

export default instance;


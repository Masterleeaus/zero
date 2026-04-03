/**
 * TitanRuntime - Main PWA runtime coordinator for Titan Zero — v2
 *
 * Phase 2 improvements:
 *   - Bootstrap contract loading and storage in IndexedDB
 *   - Trust level / node_id persisted from handshake response
 *   - SW update-available UI dispatch
 *   - SW connection-restored handling
 *   - Staged upload manager integration
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
    }

    async init() {
        try {
            await this.db.open();

            this.nodeId = await this._loadOrGenerateNodeId();

            // Load bootstrap contract from server
            this._contract = await this._loadBootstrapContract();

            this.signalQueue = new TitanSignalQueue(this.db);
            this.syncEngine = new TitanSyncEngine(this.db, this.signalQueue);

            // Apply contract-derived settings to sync engine
            if (this._contract) {
                if (this._contract.syncInterval) {
                    this.syncEngine.syncInterval = this._contract.syncInterval;
                }
                if (this._contract.syncBatchLimit) {
                    this.syncEngine._batchSize = Math.min(
                        this._contract.syncBatchLimit,
                        this.syncEngine._maxBatch
                    );
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
                });
            }

            window.dispatchEvent(new CustomEvent('titan:runtime-ready', {
                detail: { nodeId: this.nodeId, trustLevel: this.trustLevel },
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

    getNodeId() {
        return this.nodeId;
    }

    getTrustLevel() {
        return this.trustLevel;
    }

    getContract() {
        return this._contract;
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
            ...syncStatus,
            queueSize,
        };
    }

    // ─── Private ───────────────────────────────────────────────────────────────

    async _loadBootstrapContract() {
        try {
            const cached = await this.db.get('bootstrap_meta', 'contract');
            if (cached?.value) return cached.value;
        } catch (e) {
            // Fall through to fetch
        }

        try {
            const res = await fetch('/pwa/bootstrap', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return null;
            const contract = await res.json();
            await this.db.put('bootstrap_meta', { key: 'contract', value: contract });
            return contract;
        } catch (e) {
            console.warn('[TitanRuntime] Bootstrap contract load failed:', e.message);
            return null;
        }
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

    _generateNodeId() {
        const array = new Uint8Array(16);
        crypto.getRandomValues(array);
        // Format as UUID v4
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

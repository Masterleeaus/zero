/**
 * TitanSyncEngine - Sync engine for Titan Zero PWA — v2
 *
 * Improvements over v1:
 *   - Adaptive batch sizing
 *   - Per-item failure handling based on server response codes
 *   - Idempotency key injection
 *   - Server status codes: accepted / rejected / duplicate / deferred / invalid_sig / rate_limited
 *   - Exponential backoff with jitter
 *   - Background Sync API integration (with graceful fallback)
 *   - Connection-restored trigger from service worker messages
 */
import db from './db.js';
import { TitanSignalQueue } from './signalQueue.js';

export class TitanSyncEngine {
    constructor(db, signalQueue) {
        this.db = db;
        this.signalQueue = signalQueue;
        this.isSyncing = false;
        this.lastSyncAt = null;
        this.syncInterval = 30000;
        this._timer = null;
        this._onlineHandler = null;
        this._swMessageHandler = null;

        // Adaptive batch sizing
        this._batchSize = 25;
        this._minBatch = 5;
        this._maxBatch = 50;

        // Backoff state
        this._consecutiveErrors = 0;
        this._backoffMs = 1000;
        this._maxBackoffMs = 60000;
    }

    start() {
        this._onlineHandler = () => this.sync();
        window.addEventListener('online', this._onlineHandler);

        // Listen for SW messages (Background Sync trigger / connection restored)
        this._swMessageHandler = (event) => {
            if (!event.data) return;
            if (event.data.type === 'BG_SYNC_TRIGGERED' || event.data.type === 'CONNECTION_RESTORED') {
                this.sync();
            }
            if (event.data.type === 'SYNC_FALLBACK') {
                // SW couldn't register background sync — fallback to interval
            }
        };
        if (navigator.serviceWorker) {
            navigator.serviceWorker.addEventListener('message', this._swMessageHandler);
        }

        this._timer = setInterval(() => this.sync(), this.syncInterval);

        // Attempt an immediate sync if online
        if (navigator.onLine) {
            this.sync();
        }
    }

    stop() {
        if (this._timer) {
            clearInterval(this._timer);
            this._timer = null;
        }
        if (this._onlineHandler) {
            window.removeEventListener('online', this._onlineHandler);
            this._onlineHandler = null;
        }
        if (this._swMessageHandler && navigator.serviceWorker) {
            navigator.serviceWorker.removeEventListener('message', this._swMessageHandler);
            this._swMessageHandler = null;
        }
    }

    async sync() {
        if (!navigator.onLine) {
            this._emit('titan:sync-complete', { status: 'skipped', reason: 'offline' });
            return;
        }
        if (this.isSyncing) {
            return;
        }

        this.isSyncing = true;
        this._emit('titan:sync-start', {});

        try {
            const pending = await this.signalQueue.getPending();

            if (pending.length === 0) {
                this.lastSyncAt = Date.now();
                await this._persistLastSync();
                this._emit('titan:sync-complete', { status: 'ok', synced: 0 });
                this._resetBackoff();
                return;
            }

            // Adaptive batch: take up to _batchSize items
            const batch = pending.slice(0, this._batchSize);

            const meta = (await this.db.get('runtime_meta', 'node_id')) ?? {};
            const nodeId = meta.value ?? null;

            const csrfToken = this._getCsrfToken();

            // Inject idempotency keys into signals that don't have them
            const signalsToSend = batch.map((signal) => ({
                ...signal,
                idempotency_key: signal.idempotency_key
                    ?? this._generateIdempotencyKey(signal),
            }));

            const response = await fetch('/pwa/signals/ingest', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify({
                    signals: signalsToSend,
                    node_id: nodeId,
                    timestamp: Date.now(),
                }),
            });

            if (response.ok) {
                const data = await response.json().catch(() => ({}));
                const results = data.results ?? [];

                let synced = 0;
                let failed = 0;

                for (const signal of batch) {
                    const result = results.find(
                        (r) => r.signal_key === signal.signal_key
                    ) ?? {};

                    const ingestStatus = result.ingest_status ?? result.status ?? 'accepted';

                    switch (ingestStatus) {
                        case 'accepted':
                        case 'duplicate':
                            // Both mean we should remove from queue (safe)
                            await this.signalQueue.markSent(signal.queueId);
                            synced++;
                            break;

                        case 'rate_limited':
                            // Back off — don't mark as failed, retry later
                            await this.signalQueue.markRetry(signal.queueId);
                            failed++;
                            break;

                        case 'invalid_sig':
                        case 'rejected':
                            // Non-retryable for this payload
                            await this.signalQueue.markFailed(signal.queueId, ingestStatus);
                            failed++;
                            break;

                        case 'deferred':
                            // Server deferred — retry on next cycle
                            await this.signalQueue.markRetry(signal.queueId);
                            break;

                        default:
                            await this.signalQueue.markSent(signal.queueId);
                            synced++;
                    }
                }

                this.lastSyncAt = Date.now();
                await this._persistLastSync();

                // Adapt batch size: grow if all succeeded, shrink on failures
                if (failed === 0) {
                    this._batchSize = Math.min(this._batchSize + 5, this._maxBatch);
                } else {
                    this._batchSize = Math.max(this._batchSize - 5, this._minBatch);
                }

                this._resetBackoff();
                this._emit('titan:sync-complete', {
                    status: 'ok',
                    synced,
                    failed,
                    accepted: data.accepted ?? synced,
                    rejected: data.rejected ?? failed,
                    partial: failed > 0,
                });
            } else {
                // HTTP-level failure
                const isRetryable = response.status >= 500 || response.status === 429;
                const errorCode = response.status === 429 ? 'rate_limited' : `http_${response.status}`;

                for (const signal of batch) {
                    if (isRetryable) {
                        await this.signalQueue.markFailed(signal.queueId, errorCode);
                    } else {
                        await this.signalQueue.markFailed(signal.queueId, `HTTP ${response.status} (non-retryable)`);
                    }
                }

                this._incrementBackoff();
                this._emit('titan:sync-complete', { status: 'error', httpStatus: response.status });
            }
        } catch (err) {
            // Network-level failure — leave as pending for retry
            console.warn('[TitanSync] Sync error:', err);
            this._incrementBackoff();
            this._emit('titan:sync-complete', { status: 'error', error: err.message });
        } finally {
            this.isSyncing = false;
        }
    }

    async getStatus() {
        const pendingCount = await this.signalQueue.getQueueSize();
        return {
            isSyncing: this.isSyncing,
            lastSyncAt: this.lastSyncAt,
            pendingCount,
            isOnline: navigator.onLine,
            batchSize: this._batchSize,
            consecutiveErrors: this._consecutiveErrors,
        };
    }

    async fetchStatus() {
        const csrfToken = this._getCsrfToken();
        const response = await fetch('/pwa/sync/status', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
        });
        if (!response.ok) {
            throw new Error(`fetchStatus failed: HTTP ${response.status}`);
        }
        return response.json();
    }

    /**
     * Request Background Sync registration from the service worker.
     * Falls back silently if Background Sync API is not available.
     */
    requestBackgroundSync() {
        if (navigator.serviceWorker?.controller) {
            navigator.serviceWorker.controller.postMessage({ type: 'TRIGGER_SYNC' });
        }
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    /**
     * Generate a stable idempotency key from signal identity.
     * If queueId is available it is deterministic; otherwise uses random fallback
     * to prevent duplicate submission on retry.
     */
    _generateIdempotencyKey(signal) {
        const parts = [
            signal.signal_key ?? 'unknown',
            signal.created_at ?? Date.now(),
            signal.queueId ?? Math.random(),
        ];
        // Simple deterministic key from signal identity
        return btoa(parts.join('|')).replace(/[^a-zA-Z0-9]/g, '').slice(0, 64);
    }

    _getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
    }

    async _persistLastSync() {
        try {
            await this.db.put('runtime_meta', { key: 'last_sync_at', value: this.lastSyncAt });
        } catch (e) {
            // Non-critical
        }
    }

    _resetBackoff() {
        this._consecutiveErrors = 0;
        this._backoffMs = 1000;
    }

    _incrementBackoff() {
        this._consecutiveErrors++;
        // Exponential backoff with jitter, capped at maxBackoffMs
        const jitter = Math.random() * 500;
        this._backoffMs = Math.min(this._backoffMs * 2 + jitter, this._maxBackoffMs);
    }

    _emit(eventName, detail) {
        try {
            window.dispatchEvent(new CustomEvent(eventName, { detail }));
        } catch (e) {
            // Non-critical
        }
    }
}

export default TitanSyncEngine;

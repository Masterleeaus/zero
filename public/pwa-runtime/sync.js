/**
 * TitanSyncEngine - Sync engine for Titan Zero PWA
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
    }

    start() {
        this._onlineHandler = () => this.sync();
        window.addEventListener('online', this._onlineHandler);

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

        try {
            const pending = await this.signalQueue.getPending();

            if (pending.length === 0) {
                this.lastSyncAt = Date.now();
                await this._persistLastSync();
                this._emit('titan:sync-complete', { status: 'ok', synced: 0 });
                return;
            }

            const meta = (await this.db.get('runtime_meta', 'node_id')) ?? {};
            const nodeId = meta.value ?? null;

            const csrfToken = this._getCsrfToken();

            const response = await fetch('/pwa/signals/ingest', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify({
                    signals: pending,
                    node_id: nodeId,
                    timestamp: Date.now(),
                }),
            });

            if (response.ok) {
                for (const signal of pending) {
                    await this.signalQueue.markSent(signal.queueId);
                }
                this.lastSyncAt = Date.now();
                await this._persistLastSync();
                this._emit('titan:sync-complete', { status: 'ok', synced: pending.length });
            } else {
                // Retry policy:
                // - 5xx (server errors): increment retry_count; markFailed sets status='failed' after 3 attempts
                // - 4xx (client errors): marked failed immediately (non-retryable payload issue)
                // - Network errors (catch block): signals stay 'pending', retried on next cycle
                const isRetryable = response.status >= 500;
                for (const signal of pending) {
                    if (isRetryable) {
                        await this.signalQueue.markFailed(signal.queueId, `HTTP ${response.status}`);
                    } else {
                        await this.signalQueue.markFailed(signal.queueId, `HTTP ${response.status} (non-retryable)`);
                    }
                }
                this._emit('titan:sync-complete', { status: 'error', httpStatus: response.status });
            }
        } catch (err) {
            // Network-level failure — leave as pending for retry
            console.warn('[TitanSync] Sync error:', err);
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

    _emit(eventName, detail) {
        try {
            window.dispatchEvent(new CustomEvent(eventName, { detail }));
        } catch (e) {
            // Non-critical
        }
    }
}

export default TitanSyncEngine;

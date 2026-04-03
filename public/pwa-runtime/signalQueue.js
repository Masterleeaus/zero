/**
 * TitanSignalQueue - Signal queue manager for Titan Zero PWA
 */
export class TitanSignalQueue {
    constructor(db) {
        this.db = db;
    }

    async enqueue(signalKey, payload, meta = {}) {
        const now = Date.now();

        const signalRecord = {
            signal_key: signalKey,
            payload,
            meta,
            created_at: now,
        };
        const localId = await this.db.put('signals_local', signalRecord);

        const queueRecord = {
            signal_key: signalKey,
            payload,
            meta,
            localId,
            status: 'pending',
            retry_count: 0,
            created_at: now,
            sent_at: null,
        };
        return this.db.put('sync_queue', queueRecord);
    }

    async getPending() {
        const db = await this.db.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('sync_queue', 'readonly');
            const store = tx.objectStore('sync_queue');
            const index = store.index('status');
            const request = index.getAll('pending');
            request.onsuccess = () => resolve(request.result ?? []);
            request.onerror = () => reject(new Error(`getPending failed: ${request.error}`));
        });
    }

    async markSent(queueId) {
        const record = await this.db.get('sync_queue', queueId);
        if (!record) return;
        record.status = 'sent';
        record.sent_at = Date.now();
        return this.db.put('sync_queue', record);
    }

    async markFailed(queueId, error) {
        const record = await this.db.get('sync_queue', queueId);
        if (!record) return;
        record.retry_count = (record.retry_count || 0) + 1;
        record.last_error = error ? String(error) : 'unknown';
        record.status = record.retry_count >= 3 ? 'failed' : 'pending';
        return this.db.put('sync_queue', record);
    }

    async markRetry(queueId) {
        const record = await this.db.get('sync_queue', queueId);
        if (!record) return;
        record.status = 'pending';
        return this.db.put('sync_queue', record);
    }

    async getQueueSize() {
        const db = await this.db.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('sync_queue', 'readonly');
            const store = tx.objectStore('sync_queue');
            const index = store.index('status');
            const request = index.count('pending');
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(new Error(`getQueueSize failed: ${request.error}`));
        });
    }

    async isDuplicate(signalKey, dedupeWindowMs = 30000) {
        const db = await this.db.open();
        const cutoff = Date.now() - dedupeWindowMs;
        return new Promise((resolve, reject) => {
            const tx = db.transaction('sync_queue', 'readonly');
            const store = tx.objectStore('sync_queue');
            const index = store.index('status');
            // Scan pending items and check signal_key + created_at window
            const request = index.getAll('pending');
            request.onsuccess = () => {
                const items = request.result ?? [];
                const found = items.some(
                    (item) => item.signal_key === signalKey && item.created_at >= cutoff
                );
                resolve(found);
            };
            request.onerror = () => reject(new Error(`isDuplicate failed: ${request.error}`));
        });
    }

    async pruneOld(maxAgeMs = 86400000) {
        const db = await this.db.open();
        const cutoff = Date.now() - maxAgeMs;
        return new Promise((resolve, reject) => {
            const tx = db.transaction('sync_queue', 'readwrite');
            const store = tx.objectStore('sync_queue');
            const index = store.index('status');
            const request = index.getAll('sent');
            request.onsuccess = () => {
                const items = request.result ?? [];
                const toDelete = items.filter((item) => (item.sent_at ?? 0) < cutoff);
                let pending = toDelete.length;
                if (pending === 0) {
                    resolve(0);
                    return;
                }
                let deleted = 0;
                toDelete.forEach((item) => {
                    const del = store.delete(item.queueId);
                    del.onsuccess = () => {
                        deleted++;
                        if (deleted === pending) resolve(deleted);
                    };
                    del.onerror = () => reject(new Error(`pruneOld delete failed: ${del.error}`));
                });
            };
            request.onerror = () => reject(new Error(`pruneOld failed: ${request.error}`));
        });
    }
}

export default TitanSignalQueue;

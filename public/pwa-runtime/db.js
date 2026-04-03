/**
 * TitanDB - IndexedDB wrapper for Titan Zero PWA — v2
 *
 * Phase 2 additions:
 *   - staged_uploads : offline photo/proof/note staging
 *   - bootstrap_meta : stored bootstrap contract from server
 *   - DB version bumped to 2
 */
class TitanDB {
    constructor() {
        this.DB_NAME = 'titan-zero-db';
        this.DB_VERSION = 2;
        this._db = null;
    }

    open() {
        if (this._db) return Promise.resolve(this._db);

        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.DB_NAME, this.DB_VERSION);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                const oldVersion = event.oldVersion;

                // ── Version 1 stores (created fresh or on upgrade from 0) ────
                if (oldVersion < 1) {
                    if (!db.objectStoreNames.contains('jobs')) {
                        db.createObjectStore('jobs', { keyPath: 'id', autoIncrement: false });
                    }

                    if (!db.objectStoreNames.contains('customers')) {
                        db.createObjectStore('customers', { keyPath: 'id', autoIncrement: false });
                    }

                    if (!db.objectStoreNames.contains('invoices')) {
                        db.createObjectStore('invoices', { keyPath: 'id', autoIncrement: false });
                    }

                    if (!db.objectStoreNames.contains('signals_local')) {
                        const signalsStore = db.createObjectStore('signals_local', { keyPath: 'localId', autoIncrement: true });
                        signalsStore.createIndex('signal_key', 'signal_key', { unique: false });
                    }

                    if (!db.objectStoreNames.contains('sync_queue')) {
                        const syncStore = db.createObjectStore('sync_queue', { keyPath: 'queueId', autoIncrement: true });
                        syncStore.createIndex('status', 'status', { unique: false });
                        syncStore.createIndex('created_at', 'created_at', { unique: false });
                    }

                    if (!db.objectStoreNames.contains('runtime_meta')) {
                        db.createObjectStore('runtime_meta', { keyPath: 'key', autoIncrement: false });
                    }
                }

                // ── Version 2 stores ─────────────────────────────────────────
                if (oldVersion < 2) {
                    // Staged uploads: offline photo/proof/note/document staging
                    if (!db.objectStoreNames.contains('staged_uploads')) {
                        const uploadStore = db.createObjectStore('staged_uploads', { keyPath: 'stageId', autoIncrement: true });
                        uploadStore.createIndex('type', 'type', { unique: false });
                        uploadStore.createIndex('status', 'status', { unique: false });
                        uploadStore.createIndex('job_id', 'job_id', { unique: false });
                    }

                    // Bootstrap contract stored from server
                    if (!db.objectStoreNames.contains('bootstrap_meta')) {
                        db.createObjectStore('bootstrap_meta', { keyPath: 'key', autoIncrement: false });
                    }
                }
            };

            request.onsuccess = (event) => {
                this._db = event.target.result;
                resolve(this._db);
            };

            request.onerror = (event) => {
                reject(new Error(`TitanDB open failed: ${event.target.error}`));
            };
        });
    }

    async put(storeName, record) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.put(record);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(new Error(`TitanDB put failed: ${request.error}`));
        });
    }

    async get(storeName, key) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const request = store.get(key);
            request.onsuccess = () => resolve(request.result ?? null);
            request.onerror = () => reject(new Error(`TitanDB get failed: ${request.error}`));
        });
    }

    async getAll(storeName, query = null) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const request = query ? store.getAll(query) : store.getAll();
            request.onsuccess = () => resolve(request.result ?? []);
            request.onerror = () => reject(new Error(`TitanDB getAll failed: ${request.error}`));
        });
    }

    async delete(storeName, key) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.delete(key);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(new Error(`TitanDB delete failed: ${request.error}`));
        });
    }

    async count(storeName, query = null) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const request = query ? store.count(query) : store.count();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(new Error(`TitanDB count failed: ${request.error}`));
        });
    }

    async clear(storeName) {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.clear();
            request.onsuccess = () => resolve();
            request.onerror = () => reject(new Error(`TitanDB clear failed: ${request.error}`));
        });
    }
}

const instance = new TitanDB();

if (typeof window !== 'undefined') {
    window.TitanDB = instance;
}

export default instance;

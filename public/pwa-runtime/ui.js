/**
 * TitanPwaUI - PWA UI components for Titan Zero
 */
class TitanPwaUI {
    constructor() {
        this._deferredPrompt = null;
        this._statusDot = null;
        this._queueCounter = null;
        this._lastSync = null;
        this._queuePollTimer = null;
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this._mount());
        } else {
            this._mount();
        }
    }

    _mount() {
        this.renderInstallBanner();
        this.renderOfflineIndicator();
        this.renderPendingCounter();
        this.renderSyncTimestamp();
    }

    renderInstallBanner() {
        // Capture the deferred install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this._deferredPrompt = e;

            if (sessionStorage.getItem('pwa-install-dismissed')) return;

            const existing = document.getElementById('pwa-install-banner');
            if (existing) return;

            const banner = document.createElement('div');
            banner.id = 'pwa-install-banner';
            Object.assign(banner.style, {
                position: 'fixed',
                bottom: '0',
                left: '0',
                right: '0',
                zIndex: '9999',
                background: '#1e293b',
                color: '#f1f5f9',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                padding: '12px 20px',
                fontSize: '14px',
                boxShadow: '0 -2px 8px rgba(0,0,0,0.3)',
                gap: '12px',
            });

            const text = document.createElement('span');
            text.textContent = 'Install Titan Zero for offline access';
            text.style.flex = '1';

            const installBtn = document.createElement('button');
            installBtn.textContent = 'Install';
            Object.assign(installBtn.style, {
                background: '#3b82f6',
                color: '#fff',
                border: 'none',
                borderRadius: '6px',
                padding: '6px 16px',
                cursor: 'pointer',
                fontWeight: '600',
                fontSize: '13px',
            });
            installBtn.addEventListener('click', async () => {
                if (!this._deferredPrompt) return;
                this._deferredPrompt.prompt();
                await this._deferredPrompt.userChoice;
                this._deferredPrompt = null;
                banner.remove();
            });

            const dismissBtn = document.createElement('button');
            dismissBtn.textContent = 'Dismiss';
            Object.assign(dismissBtn.style, {
                background: 'transparent',
                color: '#94a3b8',
                border: '1px solid #475569',
                borderRadius: '6px',
                padding: '6px 12px',
                cursor: 'pointer',
                fontSize: '13px',
            });
            dismissBtn.addEventListener('click', () => {
                sessionStorage.setItem('pwa-install-dismissed', '1');
                banner.remove();
            });

            banner.appendChild(text);
            banner.appendChild(installBtn);
            banner.appendChild(dismissBtn);
            document.body.appendChild(banner);
        });
    }

    renderOfflineIndicator() {
        const dot = document.createElement('div');
        dot.id = 'pwa-status-dot';
        Object.assign(dot.style, {
            position: 'fixed',
            top: '12px',
            right: '12px',
            zIndex: '9998',
            width: '12px',
            height: '12px',
            borderRadius: '50%',
            background: navigator.onLine ? '#22c55e' : '#ef4444',
            boxShadow: '0 0 4px rgba(0,0,0,0.3)',
            transition: 'background 0.3s',
        });
        dot.title = navigator.onLine ? 'Online' : 'Offline';

        document.body.appendChild(dot);
        this._statusDot = dot;

        window.addEventListener('online', () => this._setDotState('online'));
        window.addEventListener('offline', () => this._setDotState('offline'));

        window.addEventListener('titan:sync-complete', (e) => {
            const { status } = e.detail ?? {};
            if (status === 'ok') {
                this._setDotState('online');
            }
        });

        // While syncing, show amber — listen on a custom syncing signal if emitted
        window.addEventListener('titan:sync-start', () => this._setDotState('syncing'));
    }

    _setDotState(state) {
        if (!this._statusDot) return;
        const colors = { online: '#22c55e', syncing: '#f59e0b', offline: '#ef4444' };
        const labels = { online: 'Online', syncing: 'Syncing…', offline: 'Offline' };
        this._statusDot.style.background = colors[state] ?? colors.online;
        this._statusDot.title = labels[state] ?? 'Online';
    }

    renderPendingCounter() {
        const badge = document.createElement('div');
        badge.id = 'pwa-queue-counter';
        Object.assign(badge.style, {
            position: 'fixed',
            top: '10px',
            right: '32px',
            zIndex: '9997',
            background: '#f59e0b',
            color: '#fff',
            borderRadius: '10px',
            padding: '2px 7px',
            fontSize: '11px',
            fontWeight: '700',
            display: 'none',
            minWidth: '18px',
            textAlign: 'center',
        });
        document.body.appendChild(badge);
        this._queueCounter = badge;

        const update = async () => {
            try {
                if (window.TitanRuntime?.signalQueue) {
                    const count = await window.TitanRuntime.signalQueue.getQueueSize();
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : String(count);
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            } catch (e) {
                // Non-critical
            }
        };

        update();
        this._queuePollTimer = setInterval(update, 5000);
    }

    renderSyncTimestamp() {
        const label = document.createElement('div');
        label.id = 'pwa-last-sync';
        Object.assign(label.style, {
            position: 'fixed',
            bottom: '6px',
            right: '12px',
            zIndex: '9996',
            fontSize: '10px',
            color: '#94a3b8',
            display: 'none',
            pointerEvents: 'none',
        });
        document.body.appendChild(label);
        this._lastSync = label;

        window.addEventListener('titan:sync-complete', (e) => {
            const { status, synced } = e.detail ?? {};
            if (status === 'ok') {
                const now = new Date().toLocaleTimeString();
                label.textContent = `Last sync: ${now}`;
                label.style.display = 'block';
            }
        });
    }
}

const instance = new TitanPwaUI();

if (typeof window !== 'undefined') {
    window.TitanPwaUI = instance;
}

export default instance;

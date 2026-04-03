{{--
    Titan Zero PWA Runtime
    Registers service worker + loads client runtime modules
    Included at end of body in panel layout
--}}
<script>
    // Register Titan Zero service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(function (registration) {
                    // Check for updates on page load
                    registration.addEventListener('updatefound', function () {
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', function () {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New version available – dispatch event so UI can notify user
                                    window.dispatchEvent(new CustomEvent('titan:sw-update-ready'));
                                }
                            });
                        }
                    });
                })
                .catch(function (err) {
                    console.warn('[TitanZero] Service worker registration failed:', err);
                });
        });
    }
</script>

{{-- Load PWA runtime modules (ES modules, deferred) --}}
<script type="module">
    // Default exports — runtime.js and ui.js export a singleton instance as default
    import TitanRuntime from '/pwa-runtime/runtime.js';
    import TitanPwaUI from '/pwa-runtime/ui.js';

    // Expose on window for console access / Livewire interop
    window.TitanRuntime = TitanRuntime;
    window.TitanPwaUI  = TitanPwaUI;

    // Boot runtime after DOM ready (guard: may fire after DOMContentLoaded in module context)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    async function boot() {
        try {
            await TitanRuntime.init();
            TitanPwaUI.init();
        } catch (err) {
            console.warn('[TitanZero] PWA runtime boot error:', err);
        }
    }
</script>

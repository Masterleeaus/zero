<?php

namespace Modules\TitanHello\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Arr;

class InjectTitanHelloMenu
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Only modify responses that behave like normal Laravel responses
        if (!$response || !method_exists($response, 'getContent')) {
            return $response;
        }

        $html = $response->getContent();

        // Only touch HTML, skip JSON/API/etc.
        $contentType = $response->headers->get('Content-Type');
        if (!is_string($html) || ($contentType && stripos($contentType, 'text/html') === false)) {
            return $response;
        }

        // Must have an authenticated user
        $user = auth()->user();
        if (!$user) {
            return $response;
        }

        // Permission-aware visibility (fail-open if host app doesn't expose permissions)
        $entitled = true;
        try {
            $perms = [
                'titanhello.calls.view',
                'titanhello.calls.view',
                'titanhello.calls.view',
                'titanhello.calls.view',
            ];
            $hasAny = false;
            foreach ($perms as $p) {
                if (method_exists($user, 'permission') && $user->permission($p)) { $hasAny = true; break; }
                if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($p)) { $hasAny = true; break; }
            }
            // If the permission system exists and user has none, hide it.
            if ((method_exists($user, 'permission') || method_exists($user, 'hasPermissionTo')) && !$hasAny) {
                $entitled = false;
            }
        } catch (\Throwable $e) {
            $entitled = true;
        }
        if (!$entitled) {
            return $response;
        }

        // Resolve Titan Hello URL safely
        $url = null;

        if (Route::has('titanhello.calls.index')) {
            $url = route('titanhello.calls.index');
        } elseif (Route::has('titanhello.index')) {
            $url = route('titanhello.index');
        } elseif (Route::has('titanhello.home')) {
            $url = route('titanhello.home');
        }

        // If no route is registered, bail quietly (no error)
        if (!$url) {
            return $response;
        }

        // Menu label with fallbacks
        $label = __('modules.module.titan-hello');
        if ($label === 'modules.module.titan-hello') {
            $label = __('app.menu.titan-hello');
        }
        if ($label === 'app.menu.titan-hello') {
            $label = 'Titan Hello';
        }

        // The menu item HTML (WorkSuite v5.5+ / Tabler-style friendly)
        // - Works across both legacy sidebar <ul> and newer <ul class="navbar-nav">.
        // - Uses Tabler "ti" icon set (already used elsewhere in WorkSuite).
        $li = '<li class="nav-item titan-hello-menu">'
            . '<a class="nav-link" href="' . e($url) . '">' 
            . '<span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-phone-call"></i></span>'
            . '<span class="nav-link-title">' . e($label) . '</span>'
            . '</a></li>';

        // Optional settings shortcut (only if route exists + user can manage settings)
        $settingsLi = '';
        try {
            if (Route::has('titanhello.settings.index')) {
                $canSettings = true;
                if (method_exists($user, 'permission')) $canSettings = (bool) $user->permission('titanhello.settings.manage');
                if (method_exists($user, 'hasPermissionTo')) $canSettings = (bool) $user->hasPermissionTo('titanhello.settings.manage');

                if ($canSettings) {
                    $settingsUrl = route('titanhello.settings.index');
                    $settingsLabel = __('app.settings');
                    if ($settingsLabel === 'app.settings') $settingsLabel = 'Settings';
                    $settingsLabel = 'Titan Hello ' . $settingsLabel;

                    $settingsLi = '<li class="nav-item titan-hello-settings-menu">'
                        . '<a class="nav-link" href="' . e($settingsUrl) . '">' 
                        . '<span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-settings"></i></span>'
                        . '<span class="nav-link-title">' . e($settingsLabel) . '</span>'
                        . '</a></li>';
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Prevent duplicate injection if multiple middlewares / nested responses occur
        if (stripos($html, 'titan-hello-menu') !== false) {
            return $response;
        }

        // Try to inject into a likely sidebar <ul> by matching common class/id tokens.
        // This is more robust across Worksuite/SmartUI theme variations.
        $injected = false;

        $regexes = [
            // Newer WorkSuite (Tabler) typically uses navbar-nav
            '/(<ul\b[^>]*class="[^"]*(?:navbar-nav)[^"]*"[^>]*>)(.*?)(<\/ul>)/is',
            // Common legacy patterns
            '/(<ul\b[^>]*class="[^"]*(?:sidebar-menu|side-menu|sidebar|menu|nav)[^"]*"[^>]*>)(.*?)(<\/ul>)/is',
            '/(<ul\b[^>]*id="[^"]*(?:sidebarnav|side-menu|sidebar)[^"]*"[^>]*>)(.*?)(<\/ul>)/is',
        ];

        foreach ($regexes as $re) {
            $insert = $li . $settingsLi;
            $new = preg_replace($re, '$1$2' . $insert . '$3', $html, 1, $count);
            if ($count > 0 && is_string($new)) {
                $html = $new;
                $injected = true;
                break;
            }
        }

        if ($injected) {
            $response->setContent($html);
        }

        return $response;
    }
}

<?php

namespace Modules\TitanTalk\Http\Middleware;

use Closure;
use Illuminate\Support\Arr;

class InjectTitanTalkMenu
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Only touch HTML responses
        try {
            if (!method_exists($response, 'getContent')) {
                return $response;
            }

            $html = $response->getContent();
            if (!is_string($html) || $html === '') {
                return $response;
            }

            $ct = '';
            try { $ct = (string) $response->headers->get('Content-Type'); } catch (\Throwable $e) {}
            if ($ct && stripos($ct, 'text/html') === false) {
                return $response;
            }
        } catch (\Throwable $e) {
            return $response;
        }

        // Avoid double-injecting
        if (strpos($html, '<!-- TITAN_TALK_MENU -->') !== false) {
            return $response;
        }

        // Do not inject on login/guest screens
        try {
            if (function_exists('auth') && !auth()->check()) {
                return $response;
            }
        } catch (\Throwable $e) {}

        // If we can determine module is disabled, don't show it
        try {
            if (class_exists('Nwidart\\Modules\\Facades\\Module')) {
                if (!\Nwidart\Modules\Facades\Module::has('TitanTalk') || !\Nwidart\Modules\Facades\Module::isEnabled('TitanTalk')) {
                    return $response;
                }
            }
        } catch (\Throwable $e) {}

        // Permissions should never hide the entire module from the sidebar.
        // We still render the parent link; individual pages can enforce auth/permissions.

        $menuHtml = $this->buildMenuHtml();
        if ($menuHtml === '') {
            return $response;
        }

                // Prefer inserting inside the main sidebar UL (WorkSuite / Tabler)
        // We inject *before the closing </ul>* of the best candidate list.
        $candidates = [
            // Common Tabler sidebar
            '/<ul[^>]*class=(["\'])([^"\']*\bnavbar-nav\b[^"\']*)\1[^>]*>.*?<\/ul>/is',
            // WorkSuite variants
            '/<ul[^>]*id=(["\'])sidebar-menu\1[^>]*>.*?<\/ul>/is',
            '/<ul[^>]*class=(["\'])([^"\']*\bsidebar-menu\b[^"\']*)\1[^>]*>.*?<\/ul>/is',
            // Unquoted class attr (rare, but seen in some builds)
            '/<ul[^>]*class=([^\s>]*\bnavbar-nav\b[^\s>]*)[^>]*>.*?<\/ul>/is',
        ];

        foreach ($candidates as $rx) {
            if (preg_match($rx, $html, $m, PREG_OFFSET_CAPTURE)) {
                $full = $m[0][0];
                $off  = $m[0][1];

                // Insert just before the last closing </ul> in this match
                $insertPos = strripos($full, '</ul>');
                if ($insertPos !== false) {
                    $fullNew = substr($full, 0, $insertPos) . "\n" . $menuHtml . "\n" . substr($full, $insertPos);
                    $html = substr($html, 0, $off) . $fullNew . substr($html, $off + strlen($full));
                    $response->setContent($html);
                    return $response;
                }
            }
        }

        // Secondary fallback: look for a known sidebar container, then inject after its first <ul>
        $containerPatterns = [
            '/id=(["\'])navbar-menu\1/i',
            '/id=(["\'])sidebar\1/i',
            '/class=(["\'])([^"\']*\bnavbar\b[^"\']*\bvertical\b[^"\']*)\1/i',
        ];

        foreach ($containerPatterns as $crx) {
            if (preg_match($crx, $html, $cm, PREG_OFFSET_CAPTURE)) {
                $start = $cm[0][1];
                $ulPos = stripos($html, '<ul', $start);
                if ($ulPos !== false) {
                    $gt = strpos($html, '>', $ulPos);
                    if ($gt !== false) {
                        $gt++;
                        $html = substr($html, 0, $gt) . "\n" . $menuHtml . "\n" . substr($html, $gt);
                        $response->setContent($html);
                        return $response;
                    }
                }
            }
        }

        // Fallback: append before </body>
        $html = preg_replace('/<\/body>/i', $menuHtml . "\n</body>", $html, 1) ?: $html;
        $response->setContent($html);
        return $response;
    }

    protected function buildMenuHtml(): string
    {
        $items = $this->resolveMenuItems();
        if (empty($items)) {
            return '';
        }

        $label = 'Titan Talk';
        $icon  = 'ti ti-message-chatbot';
        try {
            $nav = config('titantalk-navigation');
            $label = $nav['sidebar']['label'] ?? $label;
            $icon  = $nav['sidebar']['icon'] ?? $icon;
        } catch (\Throwable $e) {}

        // Build a Tabler/WorkSuite-safe dropdown (no broken wrappers, no gaps)
        $out = [];
        $out[] = '<!-- TITAN_TALK_MENU -->';
        $out[] = '<li class="nav-item dropdown">';
        $out[] = '  <a class="nav-link dropdown-toggle" href="#navbar-titantalk" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">';
        $out[] = '    <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="' . e($icon) . '"></i></span>';
        $out[] = '    <span class="nav-link-title">' . e($label) . '</span>';
        $out[] = '  </a>';
        $out[] = '  <div class="dropdown-menu">';

        foreach ($items as $it) {
            $itemLabel = $it['label'] ?? '';
            $itemIcon  = $it['icon'] ?? '';
            $routeName = $it['route'] ?? null;
            if (!$itemLabel || !$routeName) {
                continue;
            }

            $href = '#';
            try { $href = route($routeName); } catch (\Throwable $e) {}

            $iconHtml = $itemIcon ? '<i class="' . e($itemIcon) . ' me-2"></i>' : '';
            $out[] = '    <a class="dropdown-item" href="' . e($href) . '">' . $iconHtml . e($itemLabel) . '</a>';
        }

        $out[] = '  </div>';
        $out[] = '</li>';

        return implode("\n", $out);
    }

    protected function resolveMenuItems(): array
    {
        try {
            $nav = config('titantalk-navigation');
            return Arr::get($nav, 'sidebar.items', []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function userHasAnyTitanTalkPermission(): bool
    {
        // If we cannot introspect permissions, default to showing the menu
        try {
            if (!function_exists('auth') || !auth()->check()) {
                return true;
            }
            $user = auth()->user();
            if (!$user) {
                return true;
            }

            $perms = [];
            try {
                $map = config('titantalk-permissions') ?: [];
                foreach ($map as $group) {
                    if (is_array($group)) {
                        foreach ($group as $p) {
                            if (is_string($p) && $p !== '') {
                                $perms[] = $p;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {}

            if (empty($perms)) {
                // If module doesn't declare permissions, show menu
                return true;
            }

            foreach (array_unique($perms) as $perm) {
                if (method_exists($user, 'hasPermission') && $user->hasPermission($perm)) return true;
                if (method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission([$perm])) return true;
                if (method_exists($user, 'can') && $user->can($perm)) return true;
            }

            // If permission system exists and user has none, hide menu
            return false;
        } catch (\Throwable $e) {
            return true;
        }
    }
}

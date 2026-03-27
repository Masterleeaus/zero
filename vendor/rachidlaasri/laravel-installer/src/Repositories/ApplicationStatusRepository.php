<?php

namespace RachidLaasri\LaravelInstaller\Repositories;

use App\Models\SettingTwo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ApplicationStatusRepository implements ApplicationStatusRepositoryInterface
{
    public function financePage(string $view = 'panel.admin.finance.gateways.particles.finance'): string
    {
        // No need to check the license anymore
        return $view;
    }

    public function financeLicense(): bool
    {
        // Do not check the license anymore
        return true;
    }

    // Remove the licenseType() method because it is no longer needed
    // public function licenseType(): ?string {}

    public function check(string $licenseKey, bool $installed = false): bool
    {
        // Remove license verification, nothing needs to be done
        return true;
    }

    // Delete the portal() method if it is no longer used
     public function portal() {}

    public function getVariable(string $key)
    {
        // Not related to license, can return null or a default value
        return null;
    }

    public function save($data): bool
    {
        // If saving the license is not needed, you can change or skip this logic
        return true;
    }

    public function setLicense(): void
    {
        // Remove saving the license into the database
    }

    public function generate(Request $request): bool
    {
        // Remove license check in the request
        return true;
    }

    public function next($request, Closure $next)
    {
        // Remove the license check, no need to redirect anymore
        return $next($request);
    }

    public function webhook($request)
    {
        // Remove the license check and no need to update the status anymore
        return response()->noContent();
    }

    // The appKey() method can still be kept if needed for other purposes
    public function appKey(): string
    {
        return md5(config('app.key'));
    }
}

<?php

namespace RachidLaasri\LaravelInstaller\Repositories;

use Closure;
use Illuminate\Http\Request;

interface ApplicationStatusRepositoryInterface
{
    // Returns the view of the finance page without checking the license anymore
    public function financePage(string $view = 'panel.admin.finance.gateways.particles.finance'): string;

    // No need to check the license anymore, always return true
    public function financeLicense(): bool;

    // This method may no longer be necessary, so it should be removed
    // public function licenseType(): ?string;

    // Remove check() because there is no need to check the license anymore
    // public function check(string $licenseKey, bool $installed = false): bool;

    // portal() may no longer be necessary if you don't need to store the license status
    public function portal();

    // Keep this if you still need to get some value from the application
    public function getVariable(string $key);

    // The generate() method can be kept if you still need to handle the request, not related to the license
    public function generate(Request $request): bool;

    // The setLicense() method can be removed
    // public function setLicense(): void;

    // This method may no longer be necessary
    // public function next($request, Closure $next);

    // The webhook() method can be removed
    // public function webhook($request);
}

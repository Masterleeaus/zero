<?php

declare(strict_types=1);

use App\Helpers\Classes\Helper;

it('returns vendor packages from autoload or cache', function () {
    $packages = Helper::getVendorPackages();

    $manifestPackages = array_keys(require app()->getCachedPackagesPath());

    expect($packages)
        ->toBeArray()
        ->not->toBeEmpty()
        ->toContain(...$manifestPackages);
});

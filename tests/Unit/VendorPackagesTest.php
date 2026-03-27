<?php

declare(strict_types=1);

use App\Helpers\Classes\Helper;

it('returns vendor packages from autoload or cache', function () {
    $packages = Helper::getVendorPackages();

    expect($packages)
        ->toBeArray()
        ->not->toBeEmpty()
        ->toContain('akaunting/laravel-setting');
});

<?php

namespace Byancode\OctaneWatch;

use Byancode\OctaneWatch\Commands\OctaneWatchCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class OctaneWatchServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('octane-watch')
            ->hasCommand(OctaneWatchCommand::class);
    }
}

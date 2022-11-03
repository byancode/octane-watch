<?php

namespace Byancode\OctaneWatch\Tests;

use Byancode\OctaneWatch\OctaneWatchServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        // parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            OctaneWatchServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}

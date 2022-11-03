<?php

namespace Byancode\OctaneWatch\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Byancode\OctaneWatch\OctaneWatchServiceProvider;

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

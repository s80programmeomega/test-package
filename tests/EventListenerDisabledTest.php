<?php

namespace Jonas\TestPackage\Tests;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Jonas\TestPackage\TestPackageServiceProvider;
use Orchestra\Testbench\TestCase;

class EventListenerDisabledTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [TestPackageServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $app['config']->set('test-package.enabled', true);
        $app['config']->set('test-package.persist_to_database', true);
        $app['config']->set('test-package.listen_auth_events', false); // Disabled from start
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function test_respects_listen_auth_events_config()
    {
        $user = new class {
            public $id = 1;
        };

        event(new Login('web', $user, false));

        $this->assertDatabaseCount('activity_logs', 0);
    }
}

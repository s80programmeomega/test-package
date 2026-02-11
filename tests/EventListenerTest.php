<?php

namespace Jonas\TestPackage\Tests;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Jonas\TestPackage\Models\ActivityLog;
use Jonas\TestPackage\TestPackageServiceProvider;
use Orchestra\Testbench\TestCase;

class EventListenerTest extends TestCase
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
        $app['config']->set('test-package.listen_auth_events', true);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function test_logs_user_login_event()
    {
        $user = new class {
            public $id = 1;
            public $email = 'test@example.com';
        };

        event(new Login('web', $user, false));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_login',
            'user_id' => 1,
        ]);

        $log = ActivityLog::first();
        $this->assertEquals('web', $log->data['guard']);
        $this->assertFalse($log->data['remember']);
    }

    public function test_logs_user_logout_event()
    {
        $user = new class {
            public $id = 1;
        };

        event(new Logout('web', $user));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_logout',
            'user_id' => 1,
        ]);
    }

    public function test_logs_user_registered_event()
    {
        $user = new class {
            public $id = 1;
            public $email = 'new@example.com';
        };

        event(new Registered($user));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_registered',
            'user_id' => 1,
        ]);

        $log = ActivityLog::first();
        $this->assertEquals('new@example.com', $log->data['email']);
    }

    // public function test_respects_listen_auth_events_config()
    // {
    //     // Create a fresh app instance with listen_auth_events disabled
    //     // $app = $this->createApplication();
    //     // $app['config']->set('test-package.listen_auth_events', false);

	// 			$this->refreshDatabase();

    //     config(['test-package.listen_auth_events' => false]);

    //     // Re-register the service provider to apply new config
    //     $this->app->register(TestPackageServiceProvider::class, true);

    //     $user = new class {
    //         public $id = 1;
    //     };

    //     event(new Login('web', $user, false));

    //     $this->assertDatabaseCount('activity_logs', 0);
    // }
}

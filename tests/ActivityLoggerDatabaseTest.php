<?php

namespace Jonas\TestPackage\Tests;

use Jonas\TestPackage\ActivityLogger;
use Jonas\TestPackage\Models\ActivityLog;
use Jonas\TestPackage\TestPackageServiceProvider;
use Orchestra\Testbench\TestCase;

class ActivityLoggerDatabaseTest extends TestCase
{
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
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function test_logs_are_saved_to_database()
    {
        $logger = new ActivityLogger([
            'enabled' => true,
            'persist_to_database' => true,
        ]);

        $logger->log('user_login', 1, ['ip' => '127.0.0.1']);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_login',
            'user_id' => 1,
        ]);
    }

    public function test_can_retrieve_logs_from_database()
    {
        ActivityLog::create([
            'action' => 'user_login',
            'user_id' => 1,
            'data' => ['ip' => '127.0.0.1'],
        ]);

        $logger = new ActivityLogger(['enabled' => true]);
        $logs = $logger->getFromDatabase();

        $this->assertCount(1, $logs);
        $this->assertEquals('user_login', $logs->first()->action);
    }

    public function test_can_filter_logs_by_user()
    {
        ActivityLog::create(['action' => 'login', 'user_id' => 1]);
        ActivityLog::create(['action' => 'login', 'user_id' => 2]);

        $logger = new ActivityLogger(['enabled' => true]);
        $logs = $logger->getFromDatabase(userId: 1);

        $this->assertCount(1, $logs);
        $this->assertEquals(1, $logs->first()->user_id);
    }

    public function test_respects_persist_config()
    {
        $logger = new ActivityLogger([
            'enabled' => true,
            'persist_to_database' => false,
        ]);

        $logger->log('test_action', 1);

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_can_clear_database()
    {
        ActivityLog::create(['action' => 'test', 'user_id' => 1]);

        $logger = new ActivityLogger(['enabled' => true]);
        $logger->clearDatabase();

        $this->assertDatabaseCount('activity_logs', 0);
    }
}
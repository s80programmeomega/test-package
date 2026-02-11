<?php

namespace Jonas\TestPackage\Tests;

use Jonas\TestPackage\ActivityLogger;
use Jonas\TestPackage\TestPackageServiceProvider;
use Orchestra\Testbench\TestCase;

class ActivityLoggerTest extends TestCase
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

    public function test_can_log_activity()
    {
        $logger = new ActivityLogger(['enabled' => true, 'persist_to_database' => false]);

        $logger->log('user_login', 1, ['ip' => '127.0.0.1']);

        $logs = $logger->getLogs();

        $this->assertCount(1, $logs);
        $this->assertEquals('user_login', $logs[0]['action']);
        $this->assertEquals(1, $logs[0]['user_id']);
    }

    public function test_can_clear_logs()
    {
        $logger = new ActivityLogger(['enabled' => true, 'persist_to_database' => false]);

        $logger->log('test_action', 1);
        $logger->clear();

        $this->assertCount(0, $logger->getLogs());
    }

    public function test_respects_enabled_config()
    {
        $logger = new ActivityLogger(['enabled' => false]);

        $logger->log('test_action', 1);

        $this->assertCount(0, $logger->getLogs());
    }

    public function test_service_is_bound_in_container()
    {
        $logger = app('test-package');

        $this->assertInstanceOf(ActivityLogger::class, $logger);
    }
}
<?php

namespace Jonas\TestPackage\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jonas\TestPackage\Models\ActivityLog;
use Jonas\TestPackage\TestPackageServiceProvider;
use Orchestra\Testbench\TestCase;

class ActivityLogCommandTest extends TestCase
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
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function test_list_command_shows_logs()
    {
        ActivityLog::create([
            'action' => 'user_login',
            'user_id' => 1,
            'data' => ['ip' => '127.0.0.1'],
        ]);

        $this->artisan('activity:log list')
            ->expectsOutput('Showing 1 logs')
            ->assertExitCode(0);
    }

    public function test_list_command_with_no_logs()
    {
        $this->artisan('activity:log list')
            ->expectsOutput('No activity logs found.')
            ->assertExitCode(0);
    }

    public function test_list_command_filters_by_user()
    {
        ActivityLog::create(['action' => 'login', 'user_id' => 1]);
        ActivityLog::create(['action' => 'login', 'user_id' => 2]);

        $this->artisan('activity:log list --user=1')
            ->expectsOutput('Showing 1 logs for user 1')
            ->assertExitCode(0);
    }

    public function test_clear_command_with_confirmation()
    {
        ActivityLog::create(['action' => 'test', 'user_id' => 1]);

        $this->artisan('activity:log clear')
            ->expectsConfirmation('Delete ALL 1 activity logs?', 'yes')
            ->expectsOutput('Deleted all 1 activity logs.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_clear_command_cancelled()
    {
        ActivityLog::create(['action' => 'test', 'user_id' => 1]);

        $this->artisan('activity:log clear')
            ->expectsConfirmation('Delete ALL 1 activity logs?', 'no')
            ->expectsOutput('Cancelled.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('activity_logs', 1);
    }

    public function test_stats_command()
    {
        ActivityLog::create(['action' => 'login', 'user_id' => 1]);
        ActivityLog::create(['action' => 'login', 'user_id' => 2]);
        ActivityLog::create(['action' => 'logout', 'user_id' => 1]);

        $this->artisan('activity:log stats')
            ->expectsOutput('📊 Activity Log Statistics')
            ->expectsOutput('Total logs: 3')
            ->expectsOutput('Unique users: 2')
            ->assertExitCode(0);
    }

    public function test_command_with_invalid_action()
    {
        $this->artisan('activity:log invalid')
            ->expectsOutput('Unknown action: invalid')
            ->assertExitCode(1);
    }
}

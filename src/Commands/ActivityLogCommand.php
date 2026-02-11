<?php

namespace Jonas\TestPackage\Commands;

use Illuminate\Console\Command;
use Jonas\TestPackage\ActivityLogger;
use Jonas\TestPackage\Models\ActivityLog;

class ActivityLogCommand extends Command
{
    protected $signature = 'activity:log
                           {action? : The action to perform (list|clear|stats)}
                           {--user= : Filter by user ID}
                           {--limit=50 : Number of logs to show}';

    protected $description = 'Manage activity logs';

    protected ActivityLogger $logger;

    public function __construct(ActivityLogger $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    public function handle(): int
    {
        $action = $this->argument('action') ?? $this->choice(
            'What would you like to do?',
            ['list', 'clear', 'stats'],
            'list'
        );

        return match ($action) {
            'list' => $this->listLogs(),
            'clear' => $this->clearLogs(),
            'stats' => $this->showStats(),
            default => $this->error("Unknown action: {$action}") ?? 1,
        };
    }

    protected function listLogs(): int
    {
        $userId = $this->option('user');
        $limit = (int) $this->option('limit');

        $logs = $this->logger->getFromDatabase($userId, $limit);

        if ($logs->isEmpty()) {
            $this->info('No activity logs found.');
            return 0;
        }

        $this->table(
            ['ID', 'Action', 'User ID', 'Data', 'Created At'],
            $logs->map(fn($log) => [
                $log->id,
                $log->action,
                $log->user_id ?? 'N/A',
                json_encode($log->data),
                $log->created_at->format('Y-m-d H:i:s'),
            ])
        );

        $this->info("Showing {$logs->count()} logs" . ($userId ? " for user {$userId}" : ''));

        return 0;
    }

    protected function clearLogs(): int
    {
        $userId = $this->option('user');

        if ($userId) {
            $count = ActivityLog::where('user_id', $userId)->count();

            if (!$this->confirm("Delete {$count} logs for user {$userId}?")) {
                $this->info('Cancelled.');
                return 0;
            }

            ActivityLog::where('user_id', $userId)->delete();
            $this->info("Deleted {$count} logs for user {$userId}.");
        } else {
            $count = ActivityLog::count();

            if (!$this->confirm("Delete ALL {$count} activity logs?")) {
                $this->info('Cancelled.');
                return 0;
            }

            $this->logger->clearDatabase();
            $this->info("Deleted all {$count} activity logs.");
        }

        return 0;
    }

    protected function showStats(): int
    {
        $total = ActivityLog::count();
        $users = ActivityLog::distinct('user_id')->whereNotNull('user_id')->count();
        $actions = ActivityLog::select('action')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('action')
            ->orderByDesc('count')
            ->get();

        $this->info("📊 Activity Log Statistics");
        $this->line("Total logs: {$total}");
        $this->line("Unique users: {$users}");
        $this->newLine();

        if ($actions->isNotEmpty()) {
            $this->info("Top Actions:");
            $this->table(
                ['Action', 'Count'],
                $actions->map(fn($action) => [$action->action, $action->count])
            );
        }

        return 0;
    }
}

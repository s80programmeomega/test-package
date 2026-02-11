<?php

namespace Jonas\TestPackage;

use Jonas\TestPackage\Models\ActivityLog;

class ActivityLogger
{
    protected array $logs = [];
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function log(string $action, ?int $userId = null, array $data = []): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $logData = [
            'action' => $action,
            'user_id' => $userId,
            'data' => $data,
        ];

        // Store in memory
        $this->logs[] = array_merge($logData, [
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Store in database if enabled
        if ($this->shouldPersist()) {
            ActivityLog::create($logData);
        }
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getFromDatabase(?int $userId = null, int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        $query = ActivityLog::query()
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    public function clear(): void
    {
        $this->logs = [];
    }

    public function clearDatabase(): void
    {
        ActivityLog::truncate();
    }

    protected function isEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }

    protected function shouldPersist(): bool
    {
        return $this->config['persist_to_database'] ?? true;
    }
}
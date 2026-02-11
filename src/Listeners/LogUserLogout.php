<?php

namespace Jonas\TestPackage\Listeners;

use Illuminate\Auth\Events\Logout;
use Jonas\TestPackage\ActivityLogger;

class LogUserLogout
{
    protected ActivityLogger $logger;

    public function __construct(ActivityLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Logout $event): void
    {
        $this->logger->log(
            action: 'user_logout',
            userId: $event->user?->id,
            data: [
                'guard' => $event->guard,
            ]
        );
    }
}
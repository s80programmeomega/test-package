<?php

namespace Jonas\TestPackage\Listeners;

use Illuminate\Auth\Events\Registered;
use Jonas\TestPackage\ActivityLogger;

class LogUserRegistered
{
    protected ActivityLogger $logger;

    public function __construct(ActivityLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Registered $event): void
    {
        $this->logger->log(
            action: 'user_registered',
            userId: $event->user->id,
            data: [
                'email' => $event->user->email ?? null,
            ]
        );
    }
}

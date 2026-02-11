<?php

namespace Jonas\TestPackage\Listeners;

use Illuminate\Auth\Events\Login;
use Jonas\TestPackage\ActivityLogger;

class LogUserLogin
{
    protected ActivityLogger $logger;

    public function __construct(ActivityLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Login $event): void
    {
        $this->logger->log(
            action: 'user_login',
            userId: $event->user->id,
            data: [
                'guard' => $event->guard,
                'remember' => $event->remember,
            ]
        );
    }
}

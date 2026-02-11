<?php

return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    'persist_to_database' => env('ACTIVITY_LOGGER_PERSIST', true),

    'actions' => [
        'user_login',
        'user_logout',
        'user_registered',
    ],
];

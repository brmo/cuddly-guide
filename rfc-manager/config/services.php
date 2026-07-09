<?php

return [

    'slack' => [
        'notifications' => [
            'bot' => env('SLACK_NOTIFICATION_BOT', null),
        ],

        'signing_secret' => env('SLACK_SIGNING_SECRET', null),
        'signing_secret_version' => env('SLACK_SIGNING_SECRET_VERSION', 'v0'),
    ],

];

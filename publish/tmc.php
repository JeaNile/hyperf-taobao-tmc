<?php

declare(strict_types=1);
return [
    'enable' => true,
    'conn' => [
        'default' => [
            'uri' => '',
            'app_key' => '',
            'app_secret' => '',
            'group_name' => 'default',
            'options' => [
                'reconnect_time' => 15,
                'pull_request_time' => 15,
            ],
            'service_name' => '',
            'handler' => '',
        ],
    ],
];

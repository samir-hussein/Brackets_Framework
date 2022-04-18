<?php

return [

    'defaults' => [
        'guard' => 'web'
    ],

    'guards' => [
        'web' => [
            'table' => 'users',
            'remember' => (60 * 60 * 24 * 30), // in sec
        ],

        // 'customer' => [
        //     'table' => 'customers',
        // ],
    ],
];

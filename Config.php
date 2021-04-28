<?php

global $config;

$config = [
    'MySql' => [
        'serverName' => 'localhost',
        'userName'   => 'root',
        'password'   => '',
        'dbName'     => '',
    ],

    'PayMob'   => [
        'PayMob_User_Name' => '',
        'PayMob_Password' => '',
        'PayMob_Integration_Id' => '',
    ],

    '2checkout' => [
        'merchantCode' => '',
        'privateKey' => '',
        'publicKey' => '',
        'demo' => true,
    ],

    'Mail' => [
        'MAIL_HOST' => 'smtp.example.com',
        'MAIL_PORT' => 587,
        'MAIL_USERNAME' => null,
        'MAIL_PASSWORD' => null,
    ]
];

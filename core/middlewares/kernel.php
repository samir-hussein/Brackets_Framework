<?php

return [
    'middlewares' => [
        'auth' => App\middlewares\Authentication::class,
        'api' => App\middlewares\JWTToken::class,
        'guest' => App\middlewares\Guest::class,
        'verifed' => App\middlewares\Verifed::class,
    ],
];

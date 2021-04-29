<?php

namespace App\middlewares;

class run
{
    private $providers;

    public function __construct()
    {
        $this->providers = [
            'auth' => new Authentication,
            'guest' => new Guest,
            'verifed' => new Verifed,
        ];
    }

    public function run(string $name)
    {
        $middleware = $this->providers[$name];
        $middleware->boot();
    }
}

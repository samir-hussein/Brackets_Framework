<?php

namespace App\middlewares;

use App\Request;

class run
{
    private $middlewares;
    private $request;

    public function __construct()
    {
        $this->request = new Request;

        $this->middlewares = fetchFile('middlewares/kernel.php')['middlewares'];
    }

    public function run(string $name)
    {
        $middleware = new $this->middlewares[$name];
        return $middleware->boot($this->request);
    }
}

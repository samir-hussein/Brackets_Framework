<?php

namespace App\middlewares;

use App\Request;

interface Middlewares
{
    public function boot(Request $request);
}

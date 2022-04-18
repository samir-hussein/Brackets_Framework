<?php

namespace App\middlewares;

use App\Auth;
use App\Request;

class Authentication implements Middlewares
{
    public function boot(Request $request)
    {
        if (!Auth::check())
            return redirect('/login');

        return true;
    }
}

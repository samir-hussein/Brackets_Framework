<?php

namespace App\middlewares;

use App\Auth;

class Authentication implements Middlewares
{
    public function boot()
    {
        if (!Auth::check())
            return redirect('/login');
    }
}

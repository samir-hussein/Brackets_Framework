<?php

namespace App\middlewares;

use App\Auth;
use App\Request;

class Guest implements Middlewares
{
    public function boot(Request $request)
    {
        if (Auth::check())
            return redirect('/');

        return true;
    }
}

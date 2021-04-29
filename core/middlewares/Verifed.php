<?php

namespace App\middlewares;

use App\Auth;

class Verifed implements Middlewares
{
    public function boot()
    {
        if (is_null(Auth::is_verified())) {
            return redirect('/login');
        } elseif (!Auth::is_verified()) {
            return redirect('/verify-email');
        }
    }
}

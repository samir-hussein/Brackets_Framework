<?php

namespace App\middlewares;

use App\Auth;
use App\Request;

class Verifed implements Middlewares
{
    public function boot(Request $request)
    {
        if (is_null(Auth::is_verified())) {
            return redirect('/login');
        } elseif (!Auth::is_verified()) {
            return redirect('/verify-email-view');
        }

        return true;
    }
}

<?php

namespace App\middlewares;

use App\Auth;
use App\JWT\JWT;
use App\Request;

class JWTToken implements Middlewares
{
    public function boot(Request $request)
    {
        if (!JWT::tokenValidate($request->bearerToken()))
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);

        return true;
    }
}

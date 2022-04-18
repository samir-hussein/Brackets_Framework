<?php

namespace App\JWT;

class Headers
{
    public static function makeHeaders()
    {
        $headers = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        return rtrim(strtr(base64_encode($headers), '+/', '-_'), '=');
    }
}

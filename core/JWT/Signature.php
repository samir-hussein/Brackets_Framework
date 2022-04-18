<?php

namespace App\JWT;

class Signature
{
    private static $secret;

    public static function makeSignature(string $headers, string $payload)
    {
        self::$secret = config('JWT_SECRET');
        $signature = hash_hmac('SHA256', "$headers.$payload", self::$secret, true);

        return rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }
}

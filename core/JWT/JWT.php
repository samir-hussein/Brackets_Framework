<?php

namespace App\JWT;

class JWT
{
    public static function getToken(string $userId)
    {
        $headers = Headers::makeHeaders();
        $payload = Payload::makePayload($userId);
        $signature = Signature::makeSignature($headers, $payload);

        return "$headers.$payload.$signature";
    }

    public static function setTTL(int|float $min)
    {
        Payload::setEXP($min);
    }

    public static function getTTL()
    {
        return Payload::exp();
    }

    public static function tokenValidate($token)
    {
        return Validate::isJWTValid($token);
    }

    public static function revoke(string $token)
    {
        return Blacklist::add($token);
    }
}

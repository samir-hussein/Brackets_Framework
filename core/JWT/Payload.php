<?php

namespace App\JWT;

class Payload
{
    /**
     * exp time in min
     *
     * @var integer
     */
    private static $exp = 120;

    private static $iat;

    public static function makePayload(string $userId)
    {
        self::$iat = time();

        $payload = json_encode([
            'iss' => self::getURL(),
            'iat' => self::$iat,
            'exp' => self::$iat + (self::$exp * 60),
            'jti' => uniqid(),
            'sub' => $userId,
        ]);

        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    private static function getURL()
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";

        return $url . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public static function exp()
    {
        return self::$exp * 60;
    }

    public static function setEXP(int|float $min)
    {
        self::$exp = $min;
    }
}

<?php

namespace core;

class Cookies
{

    public static function set($name, $value, $duration)
    {
        setcookie($name, $value, time() + $duration);
    }

    public static function remove($name)
    {
        unset($_COOKIE[$name]);
        setcookie($name, "", time() - 86400);
    }
}

<?php

namespace App;

class Session
{
    protected static $flash_key = 'flash';

    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $flash_messages = $_SESSION[self::$flash_key] ?? [];
        foreach ($flash_messages as $key => &$message) {
            $message['remove'] = true;
        }
        $_SESSION[self::$flash_key] = $flash_messages;
    }

    public static function set($key, $value)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        return $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        return $_SESSION[$key] ?? null;
    }

    public static function remove($key)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        unset($_SESSION[$key]);
    }

    public static function flash($key, $value)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        return $_SESSION[self::$flash_key][$key] = [
            'remove' => false,
            'value' => $value
        ];
    }

    public static function getFlash($key)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        return $_SESSION[self::$flash_key][$key]['value'] ?? false;
    }

    public function __destruct()
    {
        $flash_messages = $_SESSION[self::$flash_key] ?? [];
        foreach ($flash_messages as $key => &$message) {
            if ($message['remove']) {
                unset($flash_messages[$key]);
            }
        }
        $_SESSION[self::$flash_key] = $flash_messages;
    }
}

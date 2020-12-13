<?php

namespace core;

class Session
{
    public static function set($key,$value)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
        } 
        return $_SESSION[$key] = $value; 
    }

    public static function remove($key)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
        } 
        unset($_SESSION[$key]);
    }
}
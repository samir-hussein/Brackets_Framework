<?php

namespace App\Database_Tables;

class Migrations
{
    public function __construct()
    {
        foreach (scandir(dirname(__FILE__)) as $filename) {
            $path = dirname(__FILE__) . '/' . $filename;
            if (is_file($path)) {
                require_once $path;
            }
        }
    }
}

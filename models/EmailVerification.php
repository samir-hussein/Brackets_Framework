<?php

namespace Models;

use App\Database\Eloquent;

class EmailVerification extends Eloquent
{
    public function __construct()
    {
        self::$tableName = 'email_verification';
        self::$columnNames = ['token', 'email', 'expire_at'];
    }
}

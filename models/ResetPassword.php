<?php

namespace Models;

use App\Database\Eloquent;

class ResetPassword extends Eloquent
{
    public function __construct()
    {
        self::$tableName = 'password_reset';
        self::$columnNames = ['token', 'email', 'expire_at'];
    }
}

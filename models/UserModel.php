<?php

namespace Models;

use App\Database\Eloquent;

class UserModel extends Eloquent
{
    public function __construct()
    {
        self::$tableName = 'users';
        self::$columnNames = ['name', 'email', 'password'];
    }
}

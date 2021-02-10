<?php

namespace Models;

use App\DataBase;

class UserModel extends DataBase
{
    public function __construct()
    {
        self::$tableName = 'users';
        self::$columnNames = ['name', 'email', 'password', 'status'];
    }
}

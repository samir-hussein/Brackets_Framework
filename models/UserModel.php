<?php

namespace models;

use core\DataBase;

class UserModel extends DataBase
{
    public function __construct()
    {
        self::$tableName = 'users';
        self::$columnNames = ['name', 'email', 'password', 'status'];
    }
}

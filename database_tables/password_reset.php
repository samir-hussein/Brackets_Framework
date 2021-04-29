<?php

use App\DataBase;

DataBase::createTable('password_reset', function ($table) {
    $table->id();
    $table->String('email');
    $table->String('token');
    $table->timestamp('expire_at');
});

<?php

use App\DataBase;

DataBase::createTable('users', function ($table) {
    $table->id();
    $table->String('name');
    $table->String('email');
    $table->String('password');
    $table->timestamp('email_verifed_at', true);
    $table->unique('email');
});

<?php

use App\Database\Schema;

Schema::createTable('password_reset', function ($table) {
    $table->id();
    $table->String('email');
    $table->String('token');
    $table->timestamp('expire_at');
    $table->unique('email');
    $table->unique('token');
});

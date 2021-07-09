<?php

use App\Database\Schema;

Schema::create('password_reset', function ($table) {
    $table->string('email');
    $table->string('token');
    $table->string('expire_at');
    $table->unique('email');
    $table->unique('token');
});

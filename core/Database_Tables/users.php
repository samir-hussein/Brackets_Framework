<?php

use App\Database\Schema;

Schema::create('users', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('password');
    $table->timestamp('email_verifed_at')->nullable();
    $table->unique('email');
});

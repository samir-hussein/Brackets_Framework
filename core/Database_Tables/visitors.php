<?php

use App\Database\Schema;

Schema::create('visitors', function ($table) {
    $table->id();
    $table->string('visitor_ip');
});

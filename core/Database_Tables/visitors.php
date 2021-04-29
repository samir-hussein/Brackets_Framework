<?php

use App\Database\Schema;

Schema::createTable('visitors', function ($table) {
    $table->id();
    $table->String('visitor_ip');
});

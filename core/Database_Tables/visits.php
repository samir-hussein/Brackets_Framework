<?php

use App\Database\Schema;

Schema::create('visits', function ($table) {
    $table->bigInt('total_visits')->default(0);
    $table->bigInt('daily_visits')->default(0);
    $table->int('today', 11)->default(0);
});

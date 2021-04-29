<?php

use App\Database\Schema;

Schema::createTable('visits', function ($table) {
    $table->bigInt('total_visits', 20, false, 0);
    $table->bigInt('daily_visits', 20, false, 0);
    $table->int('today', 11, false, 0);
});

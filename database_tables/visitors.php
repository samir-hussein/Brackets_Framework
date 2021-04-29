<?php

use App\DataBase;

DataBase::createTable('visitors', function ($table) {
    $table->id();
    $table->String('visitor_ip');
});

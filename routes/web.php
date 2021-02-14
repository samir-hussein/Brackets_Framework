<?php

use App\Route;

Route::setTitle('Welcome')->setLayout('main.php')->get('/', function () {
    return view('welcome.php');
});

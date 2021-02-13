<?php

use App\Router;

Router::setTitle('Welcome')->setLayout('main.php')->get('/', function () {
    return view('welcome.php');
});

<?php

use App\Router;

Router::setTitle('Welcome')->setLayout('main.php')->view('/', 'welcome.php');

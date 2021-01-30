<?php

use core\Router;

Router::setTitle('Home')->setLayout('main.php')->view('/', 'welcome.php');

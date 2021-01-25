<?php

/**
 * @author Samir Hussein <samirhussein274@gmail.com>
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'core/autoload.php';

use core\Application;

// Database cofigration
$config = [
    'serverName' => 'localhost',
    'userName' => 'root',
    'password' => '',
    'dbName' => 'test',
];

// pass $config as a param for Application to connect database
$app = new Application($config);

$app->router->route('/', 'welcome.php');

$app->run();

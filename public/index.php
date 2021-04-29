<?php

/**
 * @author Samir Ebrahim Hussein <samirhussein274@gmail.com>
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

use App\Application;

$app = new Application($config);

if (!empty($config['MySql']['dbName'])) {
    require_once '../database_tables/run.php';
}

$app->run();

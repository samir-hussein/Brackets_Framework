<?php

/**
 * @author Samir Ebrahim Hussein <samirhussein274@gmail.com>
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

use App\Application;

$app = new Application();

$app->run();

<?php

/**
 * @author Samir Ebrahim Hussein <samirhussein274@gmail.com>
 */

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$errors = (config('APP_DEBUG') == 'true') ? 1 : 'off';
ini_set('display_errors', $errors);
ini_set('display_startup_errors', 1);
ini_set('log_errors', True);
ini_set('error_log', __DIR__ . '/../storage/log/brackets.log');
error_reporting(E_ALL);

use App\Application;

$app = new Application();

$app->run();

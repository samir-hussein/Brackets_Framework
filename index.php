<?php

/**
 * @author Samir Hussein <samirhussein274@gmail.com>
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'core/autoload.php';

// Database cofigration
include_once 'DB_Config.php';

use core\Application;

$app = new Application($config);

include_once 'Routes.php';

$app->run();

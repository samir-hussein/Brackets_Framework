<?php

require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/");
$dotenv->load();

chdir($_ENV['APP_ROOT']);
shell_exec('php -S ' . $_ENV['APP_URL']);

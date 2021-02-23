<?php

use App\Route;
use App\Request;
use App\Session;
use App\Response;
use App\Validatore;
use App\middlewares\run;

global $response, $router, $request, $sessions;

$response = new Response;
$request = new Request;
$router = new Route($request, $response);
$sessions = [];

function response()
{
    return $GLOBALS['response'];
}

function redirect(string $url)
{
    $GLOBALS['response']->redirect($url);
}

function view(string $view, array $variables = null)
{
    global $router;
    $router->renderPage($view, $variables);
}

function old($input)
{
    if (isset(Validatore::old()->$input))
        return Validatore::old()->$input;
}

function errors($input)
{
    if (isset(Validatore::errors()[$input]))
        return Validatore::errors()[$input];
}

function layout(string $layout)
{
    global $router;
    $router::$layout = $layout;
    return $router;
}

function title(string $title)
{
    global $router;
    $router::$title = $title;
    return $router;
}

function assets(string $path)
{
    echo "/assets/$path";
}

function public_path(string $path)
{
    echo "/$path";
}

function middleware(string $name)
{
    $middleware = new run;
    $middleware->run($name);
}

function flash($key)
{
    return Session::getFlash($key);
}

function startSession(string $name)
{
    ob_start();
}

function endSession(string $name)
{
    global $sessions;
    $sessions[$name] = ob_get_clean();
    return;
}

function session(string $name, string $value = null)
{
    global $sessions;
    if (!is_null($value)) $sessions[$name] = $value;
    return $sessions[$name] ?? null;
}

function extend(string $path)
{
    global $router;
    $router->setLayout($path);
}

function config($package, $property)
{
    global $config;
    return $config[$package][$property];
}

function token()
{
    return sha1(bin2hex(random_bytes(10)));
}

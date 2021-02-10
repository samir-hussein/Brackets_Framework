<?php

use App\Router;
use App\Request;
use App\Session;
use App\Response;
use App\Validatore;
use App\middlewares\run;

$response = new Response;
$request = new Request;
$router = new Router($request, $response);

function response()
{
    return $GLOBALS['response'];
}

function redirect(string $url)
{
    $GLOBALS['response']->redirect($url);
}

function view(string $view)
{
    $GLOBALS['router']->view($view);
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

function with(array $array)
{
    global $router;
    foreach ($array as $key => $value)
        $router->loadData[$key] = $value;
    return $router;
}

function val(string $key)
{
    global $router;
    return json_decode(json_encode($router->loadData[$key]), false);
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

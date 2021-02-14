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

function view(string $view, array $variables = null)
{
    $GLOBALS['router']->renderPage($view, $variables);
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

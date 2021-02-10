<?php

use core\Router;
use core\Request;
use core\Response;
use core\Validatore;

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

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

function redirect(string $url, array $variables = null)
{
    if (!is_null($variables)) {
        foreach ($variables as $key => $value) {
            Session::flash($key, $value);
        }
    }
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
    if (strpos($_SERVER['DOCUMENT_ROOT'], 'public') !== false) {
        echo "/assets/$path";
    } else {
        echo "/public/assets/$path";
    }
}

function showRoutes()
{
    global $router;
    $router->showRoutes();
}

function public_path(string $path)
{
    if (strpos($_SERVER['DOCUMENT_ROOT'], 'public') !== false) {
        echo "$path";
    } else {
        echo "public/$path";
    }
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

function editRequest($key, $value)
{
    global $request;
    $request = $request->params();
    $request = (array)$request;
    $request[$key] = $value;
    $request = (object)$request;
    return $request;
}

function addProperty($obj, $key, $value)
{
    $obj = (array)$obj;
    $obj[$key] = $value;
    $obj = (object)$obj;
    return $obj;
}

function encryptMessage(string $message)
{
    $strong = true;
    $key = openssl_random_pseudo_bytes(10, $strong);
    $cipher = "aes-128-gcm";
    if (in_array($cipher, openssl_get_cipher_methods())) {
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($message, $cipher, $key, $options = 0, $iv, $tag);
        return [
            'iv' => $iv,
            'tag' => $tag,
            'key' => $key,
            'ciphertext' => $ciphertext
        ];
    }
}

function decryptMessage($message, $key, $iv, $tag)
{
    $cipher = "aes-128-gcm";
    return openssl_decrypt($message, $cipher, $key, $options = 0, $iv, $tag);
}

function section(string $name, string $value = null)
{
    if (!is_null($value)) {
        global $sessions;
        $sessions[$name] = $value;
    } else {
        ob_start();
    }
}

function endSection(string $name)
{
    global $sessions;
    $sessions[$name] = ob_get_clean();
    return;
}

function _yield(string $name, string $value = null)
{
    global $sessions;
    if (!is_null($value) && !isset($sessions[$name])) $sessions[$name] = $value;
    return $sessions[$name] ?? null;
}

function extend(string $path)
{
    global $router;
    $router->setLayout($path);
}

function config($property)
{
    return $_ENV[$property];
}

function token()
{
    return sha1(bin2hex(random_bytes(10)));
}

function dd($variable)
{
    if (is_json($variable)) {
        header("Content-Type: application/json; charset=UTF-8");
        echo $variable;
        die;
    }
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    die;
}

function obj($variable)
{
    return json_decode(json_encode($variable));
}

function is_json($string)
{
    if (is_string($string)) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    } else return false;
}

function method(string $method)
{
    $method = strtolower($method);
    return "<input type='hidden' name='_METHOD' value='$method'>";
}

function back(array $variables = null)
{
    if (!is_null($variables)) {
        foreach ($variables as $key => $value) {
            Session::flash($key, $value);
        }
    }

    if (isset($_SERVER['HTTP_REFERER']))
        header("location: " . $_SERVER['HTTP_REFERER']);
}

function csrf()
{
    $token = token();
    $_SESSION['csrf_tokens'][] = $token;

    echo "<input type='hidden' name='__token' value=$token>";
}

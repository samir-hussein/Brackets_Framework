<?php

namespace App;

class Route
{

    public $request; // instance of class Request
    private static $route; // instance of class Route
    public $response; // instance of class Response
    public static $layout; //store page layout name
    public static $title; // store page title
    private static $middleware; // store page middleware name 
    protected static $routes = []; // store all routes of the application
    private static $middlewareArr = []; // store all middlewares of routes
    private static $regx = []; // store all regx for all routes

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        self::$route = $this;
    }

    /**
     * Set the value of middleware
     *
     * @return  self
     */
    public static function middleware(string $name)
    {
        self::$middleware = $name;
        return self::$route;
    }

    /**
     * Set the value of layout
     *
     * @return  self
     */
    public function setLayout(string $layout)
    {
        self::$layout = $layout;
    }

    public static function urlPattern(string $path)
    {
        $path = explode('/', $path);
        $i = 0;
        foreach ($path as $item) {
            if (strpos($item, '{') !== false) {
                $path[$i] = '*?([\w ]+)';
            }
            $i++;
        }
        $path = '/' . implode('\/', $path) . '/iu';
        return $path;
    }

    public static function get(string $path, $callback)
    {
        $path = self::urlPattern($path);
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }

        self::$routes['get'][$path] = $callback;
        self::$middlewareArr[$path] = self::$middleware;
        self::$regx[] = $path;
        self::$middleware = null;
    }

    public static function post(string $path, $callback)
    {
        $path = self::urlPattern($path);
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['post'][$path] = $callback;
        self::$regx[] = $path;
        self::$middleware = null;
    }

    public static function delete(string $path, $callback)
    {
        $path = self::urlPattern($path);
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['delete'][$path] = $callback;
        self::$regx[] = $path;
        self::$middleware = null;
    }

    public static function put(string $path, $callback)
    {
        $path = self::urlPattern($path);
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['put'][$path] = $callback;
        self::$regx[] = $path;
        self::$middleware = null;
    }

    public static function any(string $path, $callback)
    {
        $path = self::urlPattern($path);
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }

        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['get'][$path] = $callback;
        self::$routes['post'][$path] = $callback;
        self::$routes['delete'][$path] = $callback;
        self::$routes['put'][$path] = $callback;
        self::$regx[] = $path;
        self::$middleware = null;
    }

    public static function view(string $path, string $callback)
    {
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['view'][$path] = $callback;
        self::$middleware = null;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $ArrayParams = [];

        foreach (self::$regx as $route) {
            if (preg_match($route, urldecode($path), $url)) {
                if ($url[0] == urldecode($path)) {
                    $path = $route;
                    for ($j = 1; $j < count($url); $j++) {
                        $ArrayParams[] = $url[$j];
                    }
                    break;
                }
            }
        }

        if (isset(self::$middlewareArr[$path]) && !is_null(self::$middlewareArr[$path])) {
            middleware(self::$middlewareArr[$path]);
        }

        if (isset(self::$routes[$method][$path])) {
            $callback = self::$routes[$method][$path];
        } elseif (isset(self::$routes['view'][$path])) {
            $callback = self::$routes['view'][$path];
            if (strpos($callback, '@') !== false) {
                trigger_error('view() method render view only not making controllers', E_USER_ERROR);
            } elseif (is_string($callback)) {
                return $this->renderPage($callback);
            }
        } elseif (isset($_POST['_METHOD']) && isset(self::$routes[$_POST['_METHOD']][$path])) {
            $method = $_POST['_METHOD'];
            $callback = self::$routes[$method][$path];
        } else {
            $this->response->setStatusCode("404");
            return $this->renderPage("/404/index");
        }

        $ArrayParams[] = $this->request->params();

        if (is_callable($callback)) {
            return call_user_func_array($callback, $ArrayParams);
        }

        if (is_string($callback)) {
            $callback = explode('@', $callback);
            $callback[0] = new $callback[0];
        }

        if (is_array($callback))
            $callback[0] = new $callback[0];

        return call_user_func_array($callback, $ArrayParams);
    }

    public function renderPage(string $view, array $variables = null)
    {
        $viewContent = self::viewContent($view, $variables);
        if (!is_null(self::$layout)) {
            $layoutContent = self::layoutContent(self::$layout);
            echo $layoutContent;
            self::$layout = null;
        }
        echo $viewContent;
    }

    protected static function layoutContent(string $path)
    {
        ob_start();
        include_once __DIR__ . "/../views/$path.php";
        return ob_get_clean();
    }

    protected static function viewContent(string $view, array $variables = null)
    {
        $variables = $variables ?? [];
        ob_start();
        extract($variables);
        include_once __DIR__ . "/../views/$view.php";
        return ob_get_clean();
    }
}

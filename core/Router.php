<?php

namespace App;

use App\Request;

class Router
{

    public $request;
    public $response;
    protected static $routes = [];
    public static $layout;
    public static $title;
    private static $pathInfo = [];
    public $loadData = [];
    private static $route;
    private static $middleware;
    private static $middlewareArr;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        self::$route = $this;
    }

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
    public static function setLayout($layout)
    {
        self::$layout = $layout;
        return self::$route;
    }

    /**
     * Set the value of title
     *
     * @return  self
     */
    public static function setTitle($title)
    {
        self::$title = $title;
        return self::$route;
    }

    public static function get($path, $callback)
    {
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$routes['get'][$path] = $callback;
        self::$middlewareArr[$path] = self::$middleware;
        self::$pathInfo[$path]['layout'] = self::$layout;
        self::$pathInfo[$path]['title'] = self::$title;
        self::$layout = null;
        self::$title = null;
        self::$middleware = null;
    }

    public static function post($path, $callback)
    {
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['post'][$path] = $callback;
        self::$middleware = null;
    }

    public static function delete($path, $callback)
    {
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['delete'][$path] = $callback;
        self::$middleware = null;
    }

    public static function put($path, $callback)
    {
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['put'][$path] = $callback;
        self::$middleware = null;
    }

    public static function any($path, $callback)
    {
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['any'][$path] = $callback;
        self::$pathInfo[$path]['title'] = self::$title;
        self::$pathInfo[$path]['layout'] = self::$layout;

        self::$layout = null;
        self::$title = null;
        self::$middleware = null;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $callback = self::$routes[$method][$path] ?? false;

        if ($callback === false) {
            $callback = self::$routes['any'][$path] ?? false;
        }

        if ($callback != false) {
            self::$title = (isset(self::$pathInfo[$path]['title'])) ? self::$pathInfo[$path]['title'] : null;
            self::$layout = (isset(self::$pathInfo[$path]['layout'])) ? self::$pathInfo[$path]['layout'] : null;
        }

        if ($callback === false) {
            $callback = self::$routes['view'][$path] ?? false;
            if ($callback === false) {
                $this->response->setStatusCode("404");
                self::$layout = 'main.php';
                self::$title = 'Error 404';
                return self::view("/404/index.html");
            } else {
                if (strpos($callback, '@') !== false) {
                    trigger_error('view() method render view only not making controllers', E_USER_ERROR);
                } elseif (is_string($callback)) {
                    if (!is_null(self::$middlewareArr[$path])) {
                        middleware(self::$middlewareArr[$path]);
                    }
                    self::$title = self::$pathInfo[$path]['title'];
                    self::$layout = self::$pathInfo[$path]['layout'];
                    return $this->view($callback);
                }
            }
        }

        if (!is_null(self::$middlewareArr[$path])) {
            middleware(self::$middlewareArr[$path]);
        }

        if (!is_array($callback) && !is_string($callback)) {
            return call_user_func($callback);
        }

        if (is_string($callback)) {
            $callback = explode('@', $callback);
            $callback[0] = new $callback[0];
        }

        if (is_array($callback)) {
            $controller = new $callback[0];
            $callback[0] = $controller;
        }

        return call_user_func($callback, $this, $this->response);
    }

    public static function view()
    {
        $args = func_get_args();
        if (func_num_args() == 2) {
            $path = $args[0];
            $callback = $args[1];
            self::$middlewareArr[$path] = self::$middleware;
            self::$routes['view'][$path] = $callback;
            self::$pathInfo[$path]['title'] = self::$title;
            self::$pathInfo[$path]['layout'] = self::$layout;
            self::$layout = null;
            self::$title = null;
            self::$middleware = null;
        } else if (func_num_args() == 1) {
            $view = $args[0];
            $layoutContent = self::$route->layoutContent($view);
            $layoutContent = str_replace('{{title}}', self::$title, $layoutContent);
            $viewContent = self::$route->renderOnlyView($view);
            if (!$layoutContent) {
                echo str_replace('{{title}}', self::$title, $viewContent);
            }
            echo str_replace('{{content}}', $viewContent, $layoutContent);
        } else {
            trigger_error('Expecting at least one argument', E_USER_ERROR);
        }
    }

    protected function layoutContent($view)
    {
        $folder = explode('/', $view);
        if (count($folder) == 1) {
            $folder = '';
        } else {
            $folder = $folder[1];
        }

        if (!is_null(self::$layout) && self::$layout !== false) {
            ob_start();
            include_once __DIR__ . "/../views/$folder/layouts/" . self::$layout;
            return ob_get_clean();
        } else return false;
    }

    protected function renderOnlyView($view)
    {
        ob_start();
        include_once __DIR__ . "/../views/$view";
        return ob_get_clean();
    }
}

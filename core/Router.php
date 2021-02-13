<?php

namespace App;

class Router
{

    public $request; // instance of class Request
    private static $route; // instance of class Route
    public $response; // instance of class Response
    public static $layout; //store page layout name
    public static $title; // store page title
    private static $middleware; // store page middleware name 
    protected static $routes = []; // store all routes of the application
    private static $middlewareArr = []; // store all middlewares of routes
    private static $pathInfo = []; // store all layouts and titles of routes
    public $loadData = []; // store all data that transfer between controllers and views

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
    public static function setLayout(string $layout)
    {
        self::$layout = $layout;
        return self::$route;
    }

    /**
     * Set the value of title
     *
     * @return  self
     */
    public static function setTitle(string $title)
    {
        self::$title = $title;
        return self::$route;
    }

    public static function get(string $path, $callback)
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

    public static function post(string $path, $callback)
    {
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['post'][$path] = $callback;
        self::$middleware = null;
    }

    public static function delete(string $path, $callback)
    {
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['delete'][$path] = $callback;
        self::$middleware = null;
    }

    public static function put(string $path, $callback)
    {
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['put'][$path] = $callback;
        self::$middleware = null;
    }

    public static function any(string $path, $callback)
    {
        if (basename(debug_backtrace()[0]['file']) == "api.php") {
            $path = "/api$path";
        }

        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['get'][$path] = $callback;
        self::$routes['post'][$path] = $callback;
        self::$routes['delete'][$path] = $callback;
        self::$routes['put'][$path] = $callback;
        self::$pathInfo[$path]['title'] = self::$title;
        self::$pathInfo[$path]['layout'] = self::$layout;

        self::$layout = null;
        self::$title = null;
        self::$middleware = null;
    }

    public static function view(string $path, string $callback)
    {
        self::$middlewareArr[$path] = self::$middleware;
        self::$routes['view'][$path] = $callback;
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

        if (isset(self::$middlewareArr[$path]) && !is_null(self::$middlewareArr[$path])) {
            middleware(self::$middlewareArr[$path]);
        }

        self::$title = self::$pathInfo[$path]['title'] ?? null;
        self::$layout = self::$pathInfo[$path]['layout'] ?? null;

        if (isset(self::$routes[$method][$path])) {
            $callback = self::$routes[$method][$path];
        } elseif (isset(self::$routes['view'][$path])) {
            $callback = self::$routes['view'][$path];
            if (strpos($callback, '@') !== false) {
                trigger_error('view() method render view only not making controllers', E_USER_ERROR);
            } elseif (is_string($callback)) {
                return $this->renderPage($callback);
            }
        } else {
            $this->response->setStatusCode("404");
            self::$layout = 'main.php';
            self::$title = 'Error 404';
            return $this->renderPage("/404/index.html");
        }

        if (is_callable($callback)) {
            return call_user_func($callback);
        }

        if (is_string($callback)) {
            $callback = explode('@', $callback);
            $callback[0] = new $callback[0];
        }

        if (is_array($callback))
            $callback[0] = new $callback[0];

        return call_user_func($callback, $this, $this->response);
    }

    public function renderPage(string $view)
    {
        $layoutContent = $this->layoutContent($view);
        $layoutContent = str_replace('{{title}}', self::$title, $layoutContent);
        $viewContent = $this->viewContent($view);
        if (!$layoutContent) {
            echo str_replace('{{title}}', self::$title, $viewContent);
        }
        echo str_replace('{{content}}', $viewContent, $layoutContent);
    }

    protected function layoutContent(string $view)
    {
        $folder = explode('/', $view);
        if (count($folder) == 1) {
            $folder = '';
        } else {
            $folder = $folder[1];
        }

        if (!is_null(self::$layout)) {
            ob_start();
            include_once __DIR__ . "/../views/$folder/layouts/" . self::$layout;
            return ob_get_clean();
        } else return false;
    }

    protected function viewContent(string $view)
    {
        ob_start();
        include_once __DIR__ . "/../views/$view";
        return ob_get_clean();
    }
}

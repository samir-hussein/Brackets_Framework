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
    private static $pathInfo = []; // store all layouts and titles of routes
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
        self::$pathInfo[$path]['layout'] = self::$layout;
        self::$pathInfo[$path]['title'] = self::$title;
        self::$regx[] = $path;

        self::$layout = null;
        self::$title = null;
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
        self::$pathInfo[$path]['title'] = self::$title;
        self::$pathInfo[$path]['layout'] = self::$layout;
        self::$regx[] = $path;

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
            return call_user_func_array($callback, $ArrayParams);
        }

        if (is_string($callback)) {
            $callback = explode('@', $callback);
            $callback[0] = new $callback[0];
        }

        if (is_array($callback))
            $callback[0] = new $callback[0];

        $ArrayParams[] = $this;
        $ArrayParams[] = $this->response;

        return call_user_func_array($callback, $ArrayParams);
    }

    public function renderPage(string $view, array $variables = null)
    {
        $layoutContent = self::layoutContent($view);
        $layoutContent = str_replace('{{title}}', self::$title, $layoutContent);
        $viewContent = self::viewContent($view, $variables);
        if (!$layoutContent) {
            echo str_replace('{{title}}', self::$title, $viewContent);
        }
        echo str_replace('{{content}}', $viewContent, $layoutContent);
    }

    protected static function layoutContent(string $view)
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

    protected static function viewContent(string $view, array $variables = null)
    {
        $variables = $variables ?? [];
        ob_start();
        extract($variables);
        include_once __DIR__ . "/../views/$view";
        return ob_get_clean();
    }
}

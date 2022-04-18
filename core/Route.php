<?php

namespace App;

use function PHPUnit\Framework\isNull;

class Route
{

    public $request; // instance of class Request
    private static $route; // instance of class Route
    public $response; // instance of class Response
    public static $layout; //store page layout name
    public static $title; // store page title
    protected static $routes = []; // store all routes of the application
    private static $middlewareArr = []; // store all middlewares of routes
    private static $regx = []; // store all regx for all routes
    private static $routesNames = []; // store routes names
    private static $routesList = [];
    private static $prevPath;
    private static $prevPathPattern;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        self::$route = $this;
    }

    /**
     * Set the value of middleware
     *
     * @return  Route
     */
    public function middleware(string $name)
    {
        self::$middlewareArr[self::$prevPathPattern] = $name;
        return self::$route;
    }

    public function name(string $name)
    {
        self::$routesNames[$name] = self::$prevPath;
        return self::$route;
    }

    public function getRouteByName(string $name, array|null $parameters)
    {
        $route = self::$routesNames[$name];
        if ($parameters) {
            foreach ($parameters as $key => $val) {
                $route = str_replace('{' . $key . '}', $val, $route);
            }
        }
        return $route;
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

    public static function urlPattern(string $path, string $method)
    {
        $path = (str_starts_with($path, '/') == true) ? "$path" : "/$path";

        if (basename(debug_backtrace()[1]['file']) == "api.php") {
            $path = "/api$path";
        }

        self::$routesList[$method][] = $path;
        self::$prevPath = $path;

        $path = explode('/', $path);
        $i = 0;
        foreach ($path as $item) {
            if (strpos($item, '{') !== false) {
                $path[$i] = '*?([\w ]+)';
            }
            $i++;
        }
        $path = '/' . implode('\/', $path) . '/iu';
        self::$prevPathPattern = $path;
        return $path;
    }

    public static function get(string $path, $callback)
    {
        $path = self::urlPattern($path, 'GET');

        self::$routes['get'][$path] = $callback;
        self::$regx[] = $path;

        return self::$route;
    }

    public static function post(string $path, $callback)
    {
        $path = self::urlPattern($path, 'POST');

        self::$routes['post'][$path] = $callback;
        self::$regx[] = $path;

        return self::$route;
    }

    public static function delete(string $path, $callback)
    {
        $path = self::urlPattern($path, 'DELETE');

        self::$routes['delete'][$path] = $callback;
        self::$regx[] = $path;

        return self::$route;
    }

    public static function put(string $path, $callback)
    {
        $path = self::urlPattern($path, 'PUT');

        self::$routes['put'][$path] = $callback;
        self::$regx[] = $path;

        return self::$route;
    }

    public static function any(string $path, $callback)
    {
        $path = self::urlPattern($path, 'GET|POST|PUT|DELETE');

        self::$routes['get'][$path] = $callback;
        self::$routes['post'][$path] = $callback;
        self::$routes['delete'][$path] = $callback;
        self::$routes['put'][$path] = $callback;
        self::$regx[] = $path;

        return self::$route;
    }

    public static function view(string $path, string $callback)
    {
        $path = (str_starts_with($path, '/') == true) ? "$path" : "/$path";
        self::$routesList['GET'][] = $path;
        self::$routes['view'][$path] = $callback;

        return self::$route;
    }

    public function resolve()
    {
        if ($_ENV['CSRF_PROTECTION'] == 'true' && $this->request->getMethod() != 'get' && isset($_SERVER['HTTP_REFERER'])) {
            $token = $this->request->all()->_token ?? null;
            if (!$token || $token != Session::get('csrf-token')) {
                $this->response->setStatusCode("403");
                return $this->renderPage('/errors/general', [
                    'code' => 403,
                    'msg' => 'Access was denied',
                ]);
            }
        }

        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $ArrayParams = [];
        $ArrayParams[] = $this->request;

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
            $next = middleware(self::$middlewareArr[$path]);
            if ($next !== true) {
                return $next;
            }
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
            foreach (self::$routes as $key => $val) {
                foreach ($val as $key2 => $val2) {
                    if ($key2 == $path) {
                        $this->response->setStatusCode("405");
                        return $this->renderPage('/errors/general', [
                            'code' => 405,
                            'msg' => 'Method Not Allowed',
                            'method' => $key,
                            'given_method' => $method
                        ]);
                    }
                }
            }
            $this->response->setStatusCode("404");
            return $this->renderPage("/errors/404");
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

        return call_user_func_array($callback, $ArrayParams);
    }

    public function renderPage(string $view, array $variables = [])
    {
        $viewContent = self::viewContent($view, $variables);
        if (!is_null(self::$layout)) {
            $layoutContent = self::viewContent(self::$layout);
            echo $layoutContent;
            self::$layout = null;
        }
        echo $viewContent;
    }

    protected static function viewContent(string $view, array $variables = [])
    {
        $con = file_get_contents("../views/$view.php");
        self::templateEngine($con, $variables);
        return ob_get_clean();
    }

    public function showRoutes()
    {
        $temp = [];
        foreach (self::$routesList as $method => $route) {
            $method = ($method == 'view') ? 'get' : $method;
            foreach ($route as $key => $val) {
                $temp[$method][] = $val;
            }
        }
        dd((object)$temp);
    }

    private static function templateEngine(string $str, array $variables = [])
    {
        ob_start();
        extract($variables);
        $pattern = [
            '/\{\{([\w\(\)\'\"\/\,\$\[\]\-\.\=\>\< ]*)(?!\s)\}\}/',
            '/\@if([\w\'\=\"\$\!\>\<\&\|\(\) ]*)(?!\s)\:/',
            '/\@elseif([\w\'\=\"\$\!\>\<\&\|\(\) ]*)(?!\s)\:/',
            '/\@else/',
            '/\@endif/',
            '/\@foreach([\w\=\$\>\'\"\[\]\(\) ]*)(?!\s)\:/',
            '/\@endforeach/',
            '/\@for([\w\=\$\>\<\&\|\!\'\"\;\,\[\]\(\)\+\- ]*)(?!\s)\:/',
            '/\@endfor/',
            '/\@php/',
            '/\@endphp/',
            '/@yield(.*)\)/',
            '/\@([\w\(\'\/\"\,\$\.\- ]*)(?!\s)\)/'
        ];
        $replace = [
            '<?= htmlspecialchars($1??"") ?>',
            '<?php if$1{?>',
            '<?php }elseif$1{?>',
            '<?php }else{?>',
            '<?php }?>',
            '<?php foreach$1{?>',
            '<?php }?>',
            '<?php for$1{?>',
            '<?php }?>',
            '<?php ',
            ' ?>',
            '<?php _yield$1) ?>',
            '<?php $1) ?>'
        ];
        $str = preg_replace($pattern, $replace, $str);
        return eval(' ?>' . $str . '<?php ');
    }
}

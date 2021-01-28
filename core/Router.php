<?php

namespace core;

use core\Request;

class Router
{

    public $request;
    public $response;
    protected $routes = [];
    public $layout = "main.php";
    public $title = "";
    public $loadData = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Set the value of layout
     *
     * @return  self
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Set the value of title
     *
     * @return  self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    public function makeView($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            $this->response->setStatusCode("404");
            return $this->view("/404/index.html");
        }

        if (!is_array($callback) && !is_string($callback)) {
            return call_user_func($callback);
        }

        if (is_string($callback) && strpos($callback, '@') == false) {
            return $this->view($callback);
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

    public function view($view)
    {
        $viewContent = $this->renderOnlyView($view);
        $layoutContent = $this->layoutContent($view);
        echo str_replace('{{content}}', $viewContent, $layoutContent);
    }

    protected function layoutContent($view)
    {
        $folder = explode('/', $view);
        if (count($folder) == 1) {
            $folder = '';
        } else {
            $folder = $folder[1];
        }
        ob_start();
        include_once __DIR__ . "/../views/$folder/layouts/$this->layout";
        return ob_get_clean();
    }

    protected function renderOnlyView($view)
    {
        ob_start();
        include_once __DIR__ . "/../views/$view";
        return ob_get_clean();
    }
}

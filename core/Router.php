<?php

namespace core;

use core\Application;
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

    public function route($path, $callback = 1)
    {
        $this->routes[$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $callback = $this->routes[$path] ?? false;

        if ($callback === false) {
            $this->response->setStatusCode("404");
            return $this->renderView("/404/index.html");
        }

        if ($callback == 1) {
            $path = explode("/", $path);
            $length = count($path);
            $action = $path[$length - 1];
            $controller = "controllers\\" . $path[$length - 2];
            $callback = [$controller, $action];
        }

        if (is_string($callback)) {
            return $this->renderView($callback);
        }

        if (is_array($callback)) {
            $controller = new $callback[0];
            Application::$app->controller = $controller;
            $callback[0] = $controller;
        }

        return call_user_func($callback, $this->request, $this->response);
    }

    public function renderView($view)
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

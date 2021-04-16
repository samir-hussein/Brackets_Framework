<?php

namespace App;

class Request
{

    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');

        if ($position === false) return $path;
        return substr($path, 0, $position);
    }

    public function params()
    {
        if ($this->getMethod() == 'put' || $this->getMethod() == 'delete') {
            return json_decode(file_get_contents("php://input"));
        }
        if (isset($_POST['_METHOD'])) unset($_REQUEST['_METHOD']);
        $params = array_merge($_REQUEST, $_FILES);
        return json_decode(json_encode($params), false);
    }

    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet()
    {
        return $this->getMethod() === 'get';
    }

    public function isPost()
    {
        return $this->getMethod() === 'post';
    }
}

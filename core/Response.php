<?php

namespace App;

class Response
{

    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    public function redirect(string $url)
    {
        header("location: $url");
    }

    public function json(array $array, int $code = null)
    {
        header("Content-Type: application/json; charset=UTF-8");
        if ($code)
            $this->setStatusCode($code);
        echo json_encode($array);
    }

    public function with(array $parameters)
    {
        foreach ($parameters as $key => $val) {
            Session::put($key, $val);
        }
    }

    public function withError(string $message)
    {
        Session::putFlash('error', $message);
    }

    public function withSuccess(string $message)
    {
        Session::putFlash('success', $message);
    }
}

<?php

namespace core;

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

    public function json(array $array, int $code)
    {
        $this->setStatusCode($code);
        echo json_encode($array);
    }
}

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
        exit(header("location: $url"));
    }

    public function json(array $array, int $code)
    {
        header("Content-Type: application/json; charset=UTF-8");
        $this->setStatusCode($code);
        echo json_encode($array);
    }
}

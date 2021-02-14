<?php

namespace App;

class Application
{

    public static $app;
    public $request;
    public $response;
    public $route;
    private $db;
    private $session;

    public function __construct($config = null)
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->route = new Route($this->request, $this->response);
        self::$app = $this;
        $this->session = new Session;

        if (!empty($config['dbName'])) {
            $this->db = new DataBase($config);
        }
    }

    public function run()
    {
        return $this->route->resolve();
    }
}

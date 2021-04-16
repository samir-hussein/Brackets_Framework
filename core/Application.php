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
    private $visits;
    private $paymob;
    private $TwoCheckOut;

    public function __construct($config = null)
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->route = new Route($this->request, $this->response);
        self::$app = $this;
        $this->session = new Session;

        if (!empty($config['MySql']['dbName'])) {
            $this->db = new DataBase($config['MySql']);
            // $this->visits = new Visitors();
        }

        if (!empty($config['PayMob']['PayMob_User_Name'])) {
            $this->paymob = new PayMob($config['PayMob']);
        }

        if (!empty($config['2checkout']['merchantCode'])) {
            $this->TwoCheckOut = new TwoCheckOut($config['2checkout']);
        }
    }

    public function run()
    {
        return $this->route->resolve();
    }
}

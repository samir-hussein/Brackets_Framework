<?php

namespace App;

use App\Session;
use App\Database\DataBase;

class Application
{

    public static $app;
    public $request;
    public $response;
    public $route;

    private $session;
    private $mail;
    private $db;
    private $paymob;
    private $TwoCheckOut;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->route = new Route($this->request, $this->response);
        self::$app = $this;
        $this->session = new Session;
        $this->mail = new Mail();

        $token = Session::get('csrf-token') ?? null;
        if (!$token) {
            $token = token(35);
            Session::put('csrf-token', $token);
        }

        if (!empty($_ENV['MySql_DBName'])) {
            $this->db = new DataBase();
        }

        if (!empty($_ENV['PayMob_User_Name'])) {
            $this->paymob = new PayMob();
        }

        if (!empty($_ENV['TwoCheckOut_MerchantCode'])) {
            $this->TwoCheckOut = new TwoCheckOut();
        }
    }

    public function run()
    {
        return $this->route->resolve();
    }
}

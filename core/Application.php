<?php

namespace App;

use App\Database\Schema;
use App\Database\DataBase;
use App\Database_Tables\Migrations;

class Application
{

    public static $app;
    public $request;
    public $response;
    public $route;

    private $session;
    private $mail;
    private $db;
    private $schema;
    private $migrations;
    private $visitors;
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

        if (!empty($_ENV['MySql_DBName'])) {
            $this->db = new DataBase();
            $this->schema = new Schema();
            $this->migrations = new Migrations();
            $this->visitors = new Visitors();
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

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

    public function __construct($config = null)
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->route = new Route($this->request, $this->response);
        self::$app = $this;
        new Session;
        new Mail($config['Mail']);

        if (!empty($config['MySql']['dbName'])) {
            new DataBase($config['MySql']);
            new Schema();
            new Migrations();
            new Visitors();
        }

        if (!empty($config['PayMob']['PayMob_User_Name'])) {
            new PayMob($config['PayMob']);
        }

        if (!empty($config['2checkout']['merchantCode'])) {
            new TwoCheckOut($config['2checkout']);
        }
    }

    public function run()
    {
        return $this->route->resolve();
    }
}

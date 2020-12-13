<?php

namespace core;

use PDO;
use PDOException;

class DataBase
{
    public $serverName;
    public $userName;
    public $password;
    public $dbName;
    public static $db;
    public $conn;

    public function __construct(array $config)
    {
        $this->serverName = $config['serverName'];    
        $this->userName = $config['userName'];    
        $this->password = $config['password'];    
        $this->dbName = $config['dbName']; 
        self::$db = $this;

        try{
            $this->conn = new PDO("mysql:host=".$this->serverName.";dbname=".$this->dbName, $this->userName, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        }catch(PDOException $e){
            exit($e->getMessage());
        }
        
    }

    public function countColumn($sql)
    { 
        $stmt = $this->conn->prepare($sql); 
        $stmt->execute(); 
        $number_of_rows = $stmt->fetchColumn(); 
        return $number_of_rows;
    }

    public function prepare($sql,array $values = null)
    {
        try{
            $stmt = $this->conn->prepare($sql);
            if($values !== null){
                foreach($values as $key => $value)
                {
                    $stmt->bindValue(":$key", $value);
                }
            }
            $stmt->execute();
            if(strpos($sql, "SELECT") !== false){
                if($stmt->rowCount() > 0){
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return $result;
                }
                else return false;
            }
            return true;
        }
        catch(PDOException $e){
            exit($e->getMessage());
        }
    }
}


<?php

namespace core;

use PDO;
use PDOException;

class DataBase
{
    private $serverName;
    private $userName;
    private $password;
    private $dbName;
    private static $conn;

    protected static $tableName;
    protected static $columnNames;
    private static $where = [];
    private static $orWhere = [];
    private static $orderBy = '';

    public function __construct(array $config)
    {
        $this->serverName = $config['serverName'];
        $this->userName = $config['userName'];
        $this->password = $config['password'];
        $this->dbName = $config['dbName'];

        try {
            self::$conn = new PDO("mysql:host=" . $this->serverName . ";dbname=" . $this->dbName, $this->userName, $this->password);
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public static function countColumn($tableName)
    {
        $stmt = self::$conn->prepare("SELECT COUNT(*) FROM $tableName");
        $stmt->execute();
        $number_of_rows = $stmt->fetchColumn();
        return $number_of_rows;
    }

    public static function prepare($sql, array $values = null)
    {
        try {
            $stmt = self::$conn->prepare($sql);
            if ($values !== null) {
                foreach ($values as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
            }
            $stmt->execute();
            if (strpos($sql, "SELECT") !== false) {
                if ($stmt->rowCount() > 0) {
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return $result;
                } else return false;
            }
            return true;
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public static function insert($values)
    {
        $model = get_called_class();
        if ($model != 'core\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;
            $columns = "(";
            $placeHolders = "(";
            for ($i = 0; $i < count(self::$columnNames); $i++) {

                if ($i == (count(self::$columnNames) - 1)) {
                    $columns .= self::$columnNames[$i];
                    $placeHolders .= ":" . self::$columnNames[$i];
                } else {
                    $columns .= self::$columnNames[$i] . ",";
                    $placeHolders .= ":" . self::$columnNames[$i] . ",";
                }
            }
            $columns .= ")";
            $placeHolders .= ")";

            $sql = "INSERT INTO $tableName $columns VALUES $placeHolders";

            if (self::prepare($sql, $values)) {
                return true;
            } else return false;
        } else die("You Can Not Access This Method From DataBase Class");
    }

    public static function where($columnName, $sign, $value, $params = null)
    {
        $model = get_called_class();
        if ($model != 'core\DataBase') {
            $model = new $model;

            if ($params != null) {
                foreach ($params as $key => $value) {
                    $arr = [$columnName, $sign, $key, $value];
                }
            } else {
                $arr = [$columnName, $sign, $value];
            }
            array_push(self::$where, $arr);

            return $model;
        } else die("You Can Not Access This Method From DataBase Class");
    }

    public static function orWhere($columnName, $sign, $value, $params = null)
    {
        $model = get_called_class();
        if ($model != 'core\DataBase') {
            $model = new $model;

            if ($params != null) {
                foreach ($params as $key => $value) {
                    $arr = [$columnName, $sign, $key, $value];
                }
            } else {
                $arr = [$columnName, $sign, $value];
            }
            array_push(self::$orWhere, $arr);

            return $model;
        } else die("You Can Not Access This Method From DataBase Class");
    }

    public static function orderBy($column, $rearrange = null)
    {
        $model = get_called_class();
        if ($model != 'core\DataBase') {
            $model = new $model;

            $arrange = ($rearrange != null) ? $rearrange : '';

            self::$orderBy .= "ORDER BY $column $arrange";
            return $model;
        } else die("You Can Not Access This Method From DataBase Class");
    }

    public static function all()
    {
        $model = get_called_class();
        if ($model != 'core\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;

            $orderBy = (!empty(self::$orderBy)) ? self::$orderBy : "";

            $sql = "SELECT * FROM $tableName $orderBy";
            if ($result = self::prepare($sql)) {
                self::$orderBy = '';
                return $result;
            }
        } else die("You Can Not Access This Method From DataBase Class");
    }

    public static function get()
    {
        $tableName = self::$tableName;
        $whereString = '';
        $values = [];
        $where = self::$where;
        $orWhere = self::$orWhere;

        if (empty($orWhere) && empty($where)) {
            die("You Can Not Access This Method Without Where Or orWhere Method");
        }

        if (!empty($where)) {
            for ($i = 0; $i < count($where); $i++) {
                if (count($where[$i]) > 3) {
                    $whereString .= $where[$i][0] . " " . $where[$i][1] . " :" . $where[$i][2];

                    $values[$where[$i][2]] = $where[$i][3];
                } else {
                    $whereString .= $where[$i][0] . " " . $where[$i][1] . " '" . $where[$i][2] . "'";
                }
                if ($i != (count($where) - 1)) {
                    $whereString .= ' AND ';
                }
            }
        }
        if (!empty($orWhere)) {
            $whereString .= ' OR ';
            for ($i = 0; $i < count($orWhere); $i++) {
                if (count($orWhere[$i]) > 3) {
                    $whereString .= $orWhere[$i][0] . " " . $orWhere[$i][1] . " :" . $orWhere[$i][2];

                    $values[$orWhere[$i][2]] = $orWhere[$i][3];
                } else {
                    $whereString .= $orWhere[$i][0] . " " . $orWhere[$i][1] . " '" . $orWhere[$i][2] . "'";
                }
                if ($i != (count($orWhere) - 1)) {
                    $whereString .= ' OR ';
                }
            }
        }

        $orderBy = (!empty(self::$orderBy)) ? self::$orderBy : "";

        $sql = "SELECT * FROM $tableName WHERE $whereString $orderBy";
        if ($result = self::prepare($sql, $values)) {
            self::$where = [];
            self::$orWhere = [];
            self::$orderBy = '';
            return $result;
        }
    }

    public static function delete()
    {
        $tableName = self::$tableName;
        $whereString = '';
        $values = [];
        $where = self::$where;
        $orWhere = self::$orWhere;

        if (empty($orWhere) && empty($where)) {
            die("You Can Not Access This Method Without Where Or orWhere Method");
        }

        if (!empty($where)) {
            for ($i = 0; $i < count($where); $i++) {
                if (count($where[$i]) > 3) {
                    $whereString .= $where[$i][0] . " " . $where[$i][1] . " :" . $where[$i][2];

                    $values[$where[$i][2]] = $where[$i][3];
                } else {
                    $whereString .= $where[$i][0] . " " . $where[$i][1] . " '" . $where[$i][2] . "'";
                }
                if ($i != (count($where) - 1)) {
                    $whereString .= ' AND ';
                }
            }
        }
        if (!empty($orWhere)) {
            $whereString .= ' OR ';
            for ($i = 0; $i < count($orWhere); $i++) {
                if (count($orWhere[$i]) > 3) {
                    $whereString .= $orWhere[$i][0] . " " . $orWhere[$i][1] . " :" . $orWhere[$i][2];

                    $values[$orWhere[$i][2]] = $orWhere[$i][3];
                } else {
                    $whereString .= $orWhere[$i][0] . " " . $orWhere[$i][1] . " '" . $orWhere[$i][2] . "'";
                }
                if ($i != (count($orWhere) - 1)) {
                    $whereString .= ' OR ';
                }
            }
        }

        $sql = "DELETE FROM $tableName WHERE $whereString";
        if (self::prepare($sql, $values)) {
            self::$where = [];
            self::$orWhere = [];
            return true;
        } else return false;
    }

    public static function update($values)
    {
        $tableName = self::$tableName;
        $whereString = '';
        $updateString = '';
        $where = self::$where;
        $orWhere = self::$orWhere;

        if (empty($orWhere) && empty($where)) {
            die("You Can Not Access This Method Without Where Or orWhere Method");
        }

        if (!empty($where)) {
            for ($i = 0; $i < count($where); $i++) {
                if (count($where[$i]) > 3) {
                    $whereString .= $where[$i][0] . " " . $where[$i][1] . " :" . $where[$i][2];

                    $values[$where[$i][2]] = $where[$i][3];
                } else {
                    $whereString .= $where[$i][0] . " " . $where[$i][1] . " '" . $where[$i][2] . "'";
                }
                if ($i != (count($where) - 1)) {
                    $whereString .= ' AND ';
                }
            }
        }
        if (!empty($orWhere)) {
            $whereString .= ' OR ';
            for ($i = 0; $i < count($orWhere); $i++) {
                if (count($orWhere[$i]) > 3) {
                    $whereString .= $orWhere[$i][0] . " " . $orWhere[$i][1] . " :" . $orWhere[$i][2];

                    $values[$orWhere[$i][2]] = $orWhere[$i][3];
                } else {
                    $whereString .= $orWhere[$i][0] . " " . $orWhere[$i][1] . " '" . $orWhere[$i][2] . "'";
                }
                if ($i != (count($orWhere) - 1)) {
                    $whereString .= ' OR ';
                }
            }
        }

        $count = 0;

        foreach ($values as $key => $value) {
            if ($count != (count($values) - 1)) {
                $updateString .= $key . "=:" . $key . ", ";
            } else {
                $updateString .= $key . "=:" . $key;
            }
            $count++;
        }

        $sql = "UPDATE $tableName SET $updateString WHERE $whereString";
        if (self::prepare($sql, $values)) {
            self::$where = [];
            self::$orWhere = [];
            return true;
        } else {
            return false;
        }
    }
}

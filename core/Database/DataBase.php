<?php

namespace App\Database;

use PDO;
use PDOException;

class DataBase
{
    private $serverName;
    private $userName;
    private $password;
    private $dbName;
    protected static $conn;

    protected static $tableName;
    protected static $columnNames;
    protected static $where = [];
    protected static $orWhere = [];
    protected static $orderBy = null;

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

    public static function countRows(string $tableName = null): int
    {
        $model = get_called_class();
        if ($model != 'App\Database\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;
        }

        $whereString = self::whereString()['whereString'];
        $values = self::whereString()['values'];
        $stmt = self::$conn->prepare("SELECT COUNT(*) FROM $tableName $whereString");
        if (!empty($values)) {
            foreach ($values as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
        }
        $stmt->execute();
        $number_of_rows = $stmt->fetchColumn();
        return $number_of_rows;
    }

    public static function prepare(string $sql, array $values = null)
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
                    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
                    return $result;
                } else return null;
            }
            return true;
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    protected static function whereString(): array
    {
        $whereString = '';
        $values = [];
        $where = self::$where;
        $orWhere = self::$orWhere;

        if (!empty($where)) {
            $whereString = 'WHERE ';
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

        self::$where = [];
        self::$orWhere = [];

        return [
            'whereString' => $whereString,
            'values' => $values
        ];
    }

    public static function increment(string $columnName, int $value = 1, string $tableName = null): bool
    {
        $model = get_called_class();
        if ($model != 'App\Database\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;
        }
        $whereString = self::whereString()['whereString'];
        $values = self::whereString()['values'];

        $sql = "SELECT $columnName FROM $tableName $whereString";
        if ($result = self::prepare($sql, $values)) {
            self::$where = [];
            self::$orWhere = [];
            $increment = $result[$columnName] + $value;
            $sql = "UPDATE $tableName SET $columnName=$increment $whereString";
            if (self::prepare($sql, $values)) {
                return true;
            } else return false;
        }
    }

    public static function decrement(string $columnName, int $value = 1, string $tableName = null): bool
    {
        $model = get_called_class();
        if ($model != 'App\Database\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;
        }
        $whereString = self::whereString()['whereString'];
        $values = self::whereString()['values'];

        $sql = "SELECT $columnName FROM $tableName $whereString";
        if ($result = self::prepare($sql, $values)) {
            self::$where = [];
            self::$orWhere = [];
            $decrement = $result[$columnName] - $value;
            $sql = "UPDATE $tableName SET $columnName=$decrement $whereString";
            if (self::prepare($sql, $values)) {
                return true;
            } else return false;
        }
    }
}

<?php

namespace App;

use PDO;
use PDOException;

class DataBase
{
    private $serverName;
    private $userName;
    private $password;
    private $dbName;
    private static $conn;
    private static $instance;

    protected static $tableName;
    protected static $columnNames;
    private static $where = [];
    private static $orWhere = [];
    private static $orderBy = null;

    public function __construct(array $config)
    {
        $this->serverName = $config['serverName'];
        $this->userName = $config['userName'];
        $this->password = $config['password'];
        $this->dbName = $config['dbName'];
        self::$instance = $this;

        try {
            self::$conn = new PDO("mysql:host=" . $this->serverName . ";dbname=" . $this->dbName, $this->userName, $this->password);
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    private static function ifTableExists(string $tableName): bool
    {
        $result = self::$conn->query("SHOW TABLES LIKE '$tableName'");
        $result->execute();
        $result = $result->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) return true;
        else return false;
    }

    private static function ifColumnExists(string $tableName, string $columnName): bool
    {
        $myschema = config('MySql', 'dbName');
        $result = self::$conn->query("SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = '$myschema' AND TABLE_NAME = '$tableName' 
        AND COLUMN_NAME = '$columnName'");
        $result->execute();
        $result = $result->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) return true;
        else return false;
    }

    public static function createTable(string $tableName, callable $callback)
    {
        if (!self::ifTableExists($tableName)) {
            $table = self::$instance;
            ob_start();
            call_user_func($callback, $table);
            $query = ob_get_clean();
            $query = substr($query, 0, -1);
            $query = "CREATE TABLE $tableName ($query)";
            try {
                $stmt = self::$conn->prepare($query);
                $stmt->execute();
            } catch (PDOException $e) {
                exit($e->getMessage());
            }
        }
    }

    public static function addColumn(string $tableName, string $columnName, callable $callback)
    {
        if (self::ifTableExists($tableName)) {
            if (!self::ifColumnExists($tableName, $columnName)) {
                $table = self::$instance;
                $arg[0] = $table;
                $arg[1] = $columnName;
                ob_start();
                call_user_func_array($callback, $arg);
                $query = ob_get_clean();
                $query = explode(',', $query);
                $query = $query[0];
                $query = "ALTER TABLE $tableName ADD ($query)";
                try {
                    $stmt = self::$conn->prepare($query);
                    $stmt->execute();
                } catch (PDOException $e) {
                    exit($e->getMessage());
                }
            }
        }
    }

    public static function modifyColumn(string $tableName, string $columnName, callable $callback)
    {
        if (self::ifTableExists($tableName)) {
            if (self::ifColumnExists($tableName, $columnName)) {
                $table = self::$instance;
                $arg[0] = $table;
                $arg[1] = $columnName;
                ob_start();
                call_user_func_array($callback, $arg);
                $query = ob_get_clean();
                $query = explode(',', $query);
                $query = $query[0];
                $query = "ALTER TABLE $tableName MODIFY COLUMN $query";
                try {
                    $stmt = self::$conn->prepare($query);
                    $stmt->execute();
                } catch (PDOException $e) {
                    exit($e->getMessage());
                }
            }
        }
    }

    public static function dropColumn(string $tableName, string $columnName)
    {
        if (self::ifTableExists($tableName)) {
            if (self::ifColumnExists($tableName, $columnName)) {
                $query = "ALTER TABLE $tableName DROP COLUMN $columnName";
                try {
                    $stmt = self::$conn->prepare($query);
                    $stmt->execute();
                } catch (PDOException $e) {
                    exit($e->getMessage());
                }
            }
        }
    }

    public static function dropTable(string $tableName)
    {
        if (self::ifTableExists($tableName)) {
            $query = "DROP TABLE $tableName";
            try {
                $stmt = self::$conn->prepare($query);
                $stmt->execute();
            } catch (PDOException $e) {
                exit($e->getMessage());
            }
        }
    }

    public static function bigInt($name, $value = null, $nullable = false, $default = false, $unsigned = false)
    {
        $value = $value ?? 20;
        $default = ($default !== false) ? " DEFAULT '$default'" : '';
        $unsigned = ($unsigned !== false) ? " UNSIGNED" : '';
        $nullable = ($nullable == false) ? ' NOT NULL' : ' NULL';
        echo "$name BIGINT($value)$unsigned$default$nullable,";
    }

    public static function int($name, $value = null, $nullable = false, $default = false, $unsigned = false)
    {
        $value = $value ?? 11;
        $default = ($default !== false) ? " DEFAULT '$default'" : '';
        $unsigned = ($unsigned !== false) ? " UNSIGNED" : '';
        $nullable = ($nullable == false) ? ' NOT NULL' : ' NULL';
        echo "$name INT($value)$unsigned$default$nullable,";
    }

    public static function String($name, $value = null, $nullable = false, $default = false)
    {
        $value = $value ?? 255;
        $default = ($default !== false) ? " DEFAULT '$default'" : '';
        $nullable = ($nullable == false) ? ' NOT NULL' : ' NULL';
        echo "$name VARCHAR($value)$default$nullable,";
    }

    public static function timestamp($name, $nullable = false, $default = false)
    {
        $default = ($default !== false) ? " DEFAULT '$default'" : '';
        $nullable = ($nullable == false) ? ' NOT NULL' : ' NULL';
        echo "$name TIMESTAMP$default$nullable,";
    }

    public static function foreignKey($column1, $column2, $tableName, $ondelete, $onupdate)
    {
        echo "FOREIGN KEY ($column1) REFERENCES $tableName($column2) ON DELETE $ondelete ON UPDATE $onupdate,";
    }

    public static function unique($column)
    {
        echo "UNIQUE ($column),";
    }

    public static function id($name = null)
    {
        $name = $name ?? 'id';
        echo "$name BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,";
    }

    private static function run(string $fun, string $columnName): float
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
            $model = new $model;

            $tableName = self::$tableName;
            $whereString = self::whereString()['whereString'];
            $values = self::whereString()['values'];
            $stmt = self::$conn->prepare("SELECT $fun($columnName) FROM $tableName $whereString");
            if (!empty($values)) {
                foreach ($values as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
            }
            $stmt->execute();
            $number_of_rows = $stmt->fetchColumn();
            return $number_of_rows;
        } else trigger_error('You Can Not Access This Method From DataBase Class', E_USER_ERROR);
    }

    public static function countRows(string $tableName = null): int
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
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

    public static function sum(string $columnName): float
    {
        return self::run('SUM', $columnName);
    }

    public static function avg(string $columnName): float
    {
        return self::run('AVG', $columnName);
    }

    public static function min(string $columnName): float
    {
        return self::run('MIN', $columnName);
    }

    public static function max(string $columnName): float
    {
        return self::run('MAX', $columnName);
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

    public static function insert(array $values): bool
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;
            $columns = "(" . implode(',', self::$columnNames) . ")";
            $placeHolders = "(:" . implode(',:', self::$columnNames) . ")";

            $sql = "INSERT INTO $tableName $columns VALUES $placeHolders";

            if (self::prepare($sql, $values)) {
                return true;
            } else return false;
        } else trigger_error('You Can Not Access This Method From DataBase Class', E_USER_ERROR);
    }

    public static function where(array $value, array $param = null): object
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
            $model = new $model;
            $arr = $value;

            if ($param != null) {
                foreach ($param as $key => $value) {
                    array_pop($arr);
                    array_push($arr, $key);
                    array_push($arr, $value);
                }
            }
            array_push(self::$where, $arr);

            return $model;
        } else trigger_error('You Can Not Access This Method From DataBase Class', E_USER_ERROR);
    }

    public static function orWhere(array $value, array $param = null): object
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
            $model = new $model;
            $arr = $value;
            if ($param != null) {
                foreach ($param as $key => $value) {
                    array_pop($arr);
                    array_push($arr, $key);
                    array_push($arr, $value);
                }
            }
            array_push(self::$orWhere, $arr);

            return $model;
        } else trigger_error('You Can Not Access This Method From DataBase Class', E_USER_ERROR);
    }

    private static function whereString(): array
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

    public static function orderBy(string $column, string $rearrange = null): object
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
            $model = new $model;
            $arrange = $rearrange ?? '';
            self::$orderBy = "ORDER BY $column $arrange";
            return $model;
        } else trigger_error('You Can Not Access This Method From DataBase Class', E_USER_ERROR);
    }

    public static function all()
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;

            $orderBy = self::$orderBy ?? "";

            $sql = "SELECT * FROM $tableName $orderBy";
            if ($result = self::prepare($sql)) {
                self::$orderBy = null;
                return $result;
            } else {
                return null;
            }
        } else trigger_error('You Can Not Access This Method From DataBase Class', E_USER_ERROR);
    }

    public static function find($id)
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;

            $sql = "SELECT * FROM $tableName WHERE id=:id";
            $value = ['id' => $id];
            if ($result = self::prepare($sql, $value)) {
                return $result[0];
            } else {
                return null;
            }
        } else trigger_error('You Can Not Access This Method From DataBase Class', E_USER_ERROR);
    }

    public static function get(array $columns = null)
    {
        $tableName = self::$tableName;
        if (empty(self::$orWhere) && empty(self::$where)) {
            trigger_error('You Can Not Access This Method Without Where() Method', E_USER_ERROR);
        }
        $whereString = self::whereString()['whereString'];
        $values = self::whereString()['values'];
        $orderBy = self::$orderBy ?? "";

        if ($columns != null) {
            $columns = implode(',', $columns);
        } else {
            $columns = '*';
        }

        $sql = "SELECT $columns FROM $tableName $whereString $orderBy";
        if ($result = self::prepare($sql, $values)) {
            self::$where = [];
            self::$orWhere = [];
            self::$orderBy = null;
            return $result;
        } else {
            return null;
        }
    }

    public static function increment(string $columnName, int $value = 1, string $tableName = null): bool
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
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
        if ($model != 'App\DataBase') {
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

    public static function delete(): bool
    {
        $tableName = self::$tableName;
        if (empty(self::$orWhere) && empty(self::$where)) {
            trigger_error('You Can Not Access This Method Without Where() Method', E_USER_ERROR);
        }
        $whereString = self::whereString()['whereString'];
        $values = self::whereString()['values'];
        $sql = "DELETE FROM $tableName $whereString";
        if (self::prepare($sql, $values)) {
            self::$where = [];
            self::$orWhere = [];
            return true;
        } else return false;
    }

    public static function first()
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;
            $whereString = self::whereString()['whereString'];
            $values = (!empty(self::whereString()['values'])) ? self::whereString()['values'] : null;
            $orderBy = self::$orderBy ?? '';
            $sql = "SELECT * FROM $tableName $whereString $orderBy LIMIT 1";
            if ($result = self::prepare($sql, $values)) {
                self::$where = [];
                self::$orWhere = [];
                self::$orderBy = null;
                return $result;
            } else return null;
        } else trigger_error('You Can Not Access This Method From DataBase Class', E_USER_ERROR);
    }

    public static function update(array $params): bool
    {
        $tableName = self::$tableName;
        $updateString = '';

        if (empty(self::$orWhere) && empty(self::$where)) {
            trigger_error('You Can Not Access This Method Without Where() Method', E_USER_ERROR);
        }
        $whereString = self::whereString()['whereString'];
        $values = self::whereString()['values'];
        $count = 0;
        $values =  array_merge($values, $params);

        foreach ($params as $key => $value) {
            if ($count != (count($params) - 1)) {
                $updateString .= $key . "=:" . $key . ", ";
            } else {
                $updateString .= $key . "=:" . $key;
            }
            $count++;
        }

        $sql = "UPDATE $tableName SET $updateString $whereString";
        if (self::prepare($sql, $values)) {
            self::$where = [];
            self::$orWhere = [];
            return true;
        } else {
            return false;
        }
    }

    public static function pagination(int $page, int $records)
    {
        $model = get_called_class();
        if ($model != 'App\DataBase') {
            $model = new $model;
            $tableName = self::$tableName;
            $total_recordes = self::countRows($tableName);
            $total_pages = ceil($total_recordes / $records);
            $next = ($page >= $total_pages) ? null : ($page + 1);
            $previus = ($page > 1) ? ($page - 1) : null;
            $start = ($page - 1) * $records;
            $end = $records;
            $whereString = self::whereString()['whereString'];
            $values = self::whereString()['values'];
            $orderBy = self::$orderBy ?? '';
            $sql = "SELECT * FROM $tableName $whereString $orderBy LIMIT $start,$end";
            if ($result = self::prepare($sql, $values)) {
                self::$where = [];
                self::$orWhere = [];
                self::$orderBy = null;
                $response = [
                    'records' => $result,
                    'total_recordes' => $total_recordes,
                    'total_pages' => $total_pages,
                    'next' => $next,
                    'previus' => $previus,
                    'current_page' => $page
                ];
                return obj($response);
            } else return null;
        } else trigger_error('You Can Not Access This Method From DataBase Class', E_USER_ERROR);
    }
}

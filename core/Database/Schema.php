<?php

namespace App\Database;

use PDO;
use PDOException;

class Schema extends DataBase
{
    private static $instance;

    public function __construct()
    {
        self::$instance = $this;
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
}

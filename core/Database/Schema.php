<?php

namespace App\Database;

use PDO;
use PDOException;

class Schema extends DataBase
{
    private static $instance;
    private static $query = [];

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function hasTable(string $tableName): bool
    {
        $result = self::$conn->query("SHOW TABLES LIKE '$tableName'");
        $result->execute();
        $result = $result->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) return true;
        else return false;
    }

    public static function hasColumn(string $tableName, string $columnName): bool
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

    public static function create(string $tableName, callable $callback)
    {
        if (!self::hasTable($tableName)) {
            $table = self::$instance;
            call_user_func($callback, $table);
            $query = self::$query;
            $query = "CREATE TABLE $tableName (" . implode(',', $query) . ")";
            try {
                $stmt = self::$conn->prepare($query);
                $stmt->execute();
                self::$query = [];
            } catch (PDOException $e) {
                exit($e->getMessage());
            }
        }
    }

    public static function addColumn(string $tableName, callable $callback)
    {
        if (self::hasTable($tableName)) {
            $table = self::$instance;
            call_user_func($callback, $table);
            $query = self::$query;
            $query = implode(',', $query);
            $query = "ALTER TABLE $tableName ADD ($query)";
            try {
                $stmt = self::$conn->prepare($query);
                $stmt->execute();
                self::$query = [];
            } catch (PDOException $e) {
                exit($e->getMessage());
            }
        }
    }

    public static function modifyColumn(string $tableName, callable $callback)
    {
        if (self::hasTable($tableName)) {
            $table = self::$instance;
            call_user_func($callback, $table);
            $query = self::$query;
            $query = implode(',', $query);
            $query = "ALTER TABLE $tableName MODIFY COLUMN $query";
            try {
                $stmt = self::$conn->prepare($query);
                $stmt->execute();
                self::$query = [];
            } catch (PDOException $e) {
                exit($e->getMessage());
            }
        }
    }

    public static function dropColumn(string $tableName, string $columnName)
    {
        if (self::hasTable($tableName)) {
            if (self::hasColumn($tableName, $columnName)) {
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
        if (self::hasTable($tableName)) {
            $query = "DROP TABLE $tableName";
            try {
                $stmt = self::$conn->prepare($query);
                $stmt->execute();
            } catch (PDOException $e) {
                exit($e->getMessage());
            }
        }
    }

    public static function Default($value)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . " DEFAULT '$value'";
        return self::$instance;
    }

    public static function nullable()
    {
        self::$query[count(self::$query) - 1] = str_replace(' NOT', '', end(self::$query));
        return self::$instance;
    }

    public static function unsigned()
    {
        $query = end(self::$query);
        $query = explode(')', $query);
        self::$query[count(self::$query) - 1] = $query[0] . ') UNSIGNED' . end($query);
    }

    public static function bigInt(string $name, $value = null)
    {
        $value = $value ?? 20;
        self::$query[] = "$name BIGINT($value) NOT NULL";
        return self::$instance;
    }

    public static function int(string $name, $value = null)
    {
        $value = $value ?? 11;
        self::$query[] = "$name INT($value) NOT NULL";
        return self::$instance;
    }

    public static function string(string $name, $value = null)
    {
        $value = $value ?? 255;
        self::$query[] = "$name VARCHAR($value) NOT NULL";
        return self::$instance;
    }

    public static function enum(string $name, array $values)
    {
        $values = "'" . implode("','", $values) . "'";
        self::$query[] = "$name ENUM($values) NOT NULL";
        return self::$instance;
    }

    public static function timestamp(string $name)
    {
        self::$query[] = "$name TIMESTAMP NOT NULL";
        return self::$instance;
    }

    public static function time(string $name)
    {
        self::$query[] = "$name TIME NOT NULL";
        return self::$instance;
    }

    public static function date(string $name)
    {
        self::$query[] = "$name DATE NOT NULL";
        return self::$instance;
    }

    public static function float(string $name)
    {
        self::$query[] = "$name FLOAT NOT NULL";
        return self::$instance;
    }

    public static function double(string $name)
    {
        self::$query[] = "$name DOUBLE NOT NULL";
        return self::$instance;
    }

    public static function text(string $name)
    {
        self::$query[] = "$name TEXT NOT NULL";
        return self::$instance;
    }

    public static function mediumtext(string $name)
    {
        self::$query[] = "$name MEDIUMTEXT NOT NULL";
        return self::$instance;
    }

    public static function longtext(string $name)
    {
        self::$query[] = "$name LONGTEXT NOT NULL";
        return self::$instance;
    }

    public static function foreign(string $columnName)
    {
        self::$query[] = "FOREIGN KEY ($columnName) ";
        return self::$instance;
    }

    public static function on(string $tableName)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . "REFERENCES $tableName";
        return self::$instance;
    }

    public static function references(string $columnName)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . "($columnName)";
        return self::$instance;
    }

    public static function onDelete(string $ondelete)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . " ON DELETE $ondelete";
        return self::$instance;
    }

    public static function onUpdate(string $onupdate)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . " ON UPDATE $onupdate";
        return self::$instance;
    }

    public static function unique(string $column)
    {
        self::$query[] = "UNIQUE ($column)";
    }

    public static function id($name = null)
    {
        $name = $name ?? 'id';
        self::$query[] = "$name BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY";
        return self::$instance;
    }
}

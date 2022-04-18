<?php

namespace App\Database;

use PDO;
use PDOException;

class Schema extends DataBase
{
    private static $query = [];

    /**
     * Check if table exists in datebase
     *
     * @param string $tableName
     * @return boolean
     */
    public static function hasTable(string $tableName): bool
    {
        $result = self::$conn->query("SHOW TABLES LIKE '$tableName'");
        $result->execute();
        $result = $result->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) return true;
        else return false;
    }

    /**
     * Check if column exists in table
     *
     * @param string $tableName
     * @param string $columnName
     * @return boolean
     */
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

    /**
     * Create a table in a database
     *
     * @param string $tableName
     * @param callable $callback
     * @return void
     */
    public function create(string $tableName, callable $callback): void
    {
        if (!self::hasTable($tableName)) {
            $table = $this;
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

    /**
     * Add a column to a table
     *
     * @param string $tableName
     * @param callable $callback
     * @return void
     */
    public function addColumn(string $tableName, callable $callback): void
    {
        if (self::hasTable($tableName)) {
            $table = $this;
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

    /**
     * modify a column
     *
     * @param string $tableName
     * @param callable $callback
     * @return void
     */
    public function modifyColumn(string $tableName, callable $callback): void
    {
        if (self::hasTable($tableName)) {
            $table = $this;
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

    /**
     * Drop a column
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    public static function dropColumn(string $tableName, string $columnName): void
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

    /**
     * Drop a table
     *
     * @param string $tableName
     * @return void
     */
    public static function dropTable(string $tableName): void
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

    /**
     * Set default value to the column
     *
     * @param string|integer|boolean $value
     * @return Schema
     */
    public function default(string|int|bool $value)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . " DEFAULT '$value'";
        return $this;
    }

    /**
     * Allow null value to this column
     *
     * @return Schema
     */
    public function nullable()
    {
        self::$query[count(self::$query) - 1] = str_replace(' NOT', '', end(self::$query));
        return $this;
    }

    /**
     * Make column unsigned
     *
     * @return void
     */
    public static function unsigned()
    {
        $query = end(self::$query);
        $query = explode(')', $query);
        self::$query[count(self::$query) - 1] = $query[0] . ') UNSIGNED' . end($query);
    }

    /**
     * column with type BIGINT
     *
     * @param string $name
     * @param integer $value
     * @return Schema
     */
    public function bigInt(string $name, int $value = 20)
    {
        self::$query[] = "$name BIGINT($value) NOT NULL";
        return $this;
    }

    /**
     * column with type INT
     *
     * @param string $name
     * @param integer $value
     * @return Schema
     */
    public function int(string $name, int $value = 11)
    {
        self::$query[] = "$name INT($value) NOT NULL";
        return $this;
    }

    /**
     * column with type VARCHAR
     *
     * @param string $name
     * @param integer $value
     * @return Schema
     */
    public function string(string $name, int $value = 255)
    {
        self::$query[] = "$name VARCHAR($value) NOT NULL";
        return $this;
    }

    /**
     * column with type ENUM
     *
     * @param string $name
     * @param array $values
     * @return Schema
     */
    public function enum(string $name, array $values)
    {
        $values = "'" . implode("','", $values) . "'";
        self::$query[] = "$name ENUM($values) NOT NULL";
        return $this;
    }

    /**
     * column with type TIMESTAMP
     *
     * @param string $name
     * @return Schema
     */
    public function timestamp(string $name)
    {
        self::$query[] = "$name TIMESTAMP NOT NULL";
        return $this;
    }

    /**
     * column with type TIME
     *
     * @param string $name
     * @return Schema
     */
    public function time(string $name)
    {
        self::$query[] = "$name TIME NOT NULL";
        return $this;
    }

    /**
     * column with type DATE
     *
     * @param string $name
     * @return Schema
     */
    public function date(string $name)
    {
        self::$query[] = "$name DATE NOT NULL";
        return $this;
    }

    /**
     * column with type FLOAT
     *
     * @param string $name
     * @return Schema
     */
    public function float(string $name)
    {
        self::$query[] = "$name FLOAT NOT NULL";
        return $this;
    }

    /**
     * column with type DOUBLE
     *
     * @param string $name
     * @return Schema
     */
    public function double(string $name)
    {
        self::$query[] = "$name DOUBLE NOT NULL";
        return $this;
    }

    /**
     * column with type TEXT
     *
     * @param string $name
     * @return Schema
     */
    public function text(string $name)
    {
        self::$query[] = "$name TEXT NOT NULL";
        return $this;
    }

    /**
     * column with type MEDIUMTEXT
     *
     * @param string $name
     * @return Schema
     */
    public function mediumtext(string $name)
    {
        self::$query[] = "$name MEDIUMTEXT NOT NULL";
        return $this;
    }

    /**
     * column with type LONGTEXT
     *
     * @param string $name
     * @return Schema
     */
    public function longtext(string $name)
    {
        self::$query[] = "$name LONGTEXT NOT NULL";
        return $this;
    }

    /**
     * FOREIGN KEY
     *
     * @param string $columnName
     * @return Schema
     */
    public function foreign(string $columnName)
    {
        self::$query[] = "FOREIGN KEY ($columnName) ";
        return $this;
    }

    /**
     *
     * @param string $tableName
     * @return Schema
     */
    public function on(string $tableName)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . "REFERENCES $tableName";
        return $this;
    }

    /**
     *
     * @param string $columnName
     * @return Schema
     */
    public function references(string $columnName)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . "($columnName)";
        return $this;
    }

    /**
     *
     * @param string $ondelete
     * @return Schema
     */
    public function onDelete(string $ondelete)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . " ON DELETE $ondelete";
        return $this;
    }

    /**
     *
     * @param string $onupdate
     * @return Schema
     */
    public function onUpdate(string $onupdate)
    {
        self::$query[count(self::$query) - 1] = end(self::$query) . " ON UPDATE $onupdate";
        return $this;
    }

    /**
     * make column unique
     *
     * @param string $column
     * @return void
     */
    public function unique(string $column)
    {
        self::$query[] = "UNIQUE ($column)";
    }

    /**
     * column with name id and type BIGINT
     *
     * @param string $name
     * @return Schema
     */
    public function id(string $name = 'id')
    {
        self::$query[] = "$name BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }
}

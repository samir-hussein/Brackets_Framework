<?php

use App\Database\DataBase;
use App\Database\Schema;

require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/");
$dotenv->load();

class migration extends Schema
{
    public function __construct()
    {
        new DataBase;

        $this->create('migrations', function ($table) {
            $table->string('migration');
            $table->timestamp('created_at');
        });
    }

    public function migrate()
    {
        $classes = $this->getMigrations();
        foreach ($classes as $filename) {
            $class = "Migrations\\$filename";
            $instance = new $class;
            $instance->up();
            $time = date('Y-m-d H:i:s');
            $flag = false;
            if ($this->saveMigrate($filename, $time)) {
                echo "[$time] - migration " . $filename . " Created successfully\n";
                $flag = true;
            }
        }

        if (!$flag) echo "Nothing to migrate\n";
    }

    public function refresh()
    {
        $this->deleteMigrations();
        $classes = $this->getMigrations();
        foreach ($classes as $filename) {
            $class = "Migrations\\$filename";
            $instance = new $class;
            $instance->down();
            $instance->up();
            $time = date('Y-m-d H:i:s');
            if ($this->saveMigrate($filename, $time)) {
                echo "[$time] - migration " . $filename . " Created successfully\n";
            }
        }
    }

    private function saveMigrate($name, $time)
    {
        $result = DataBase::prepare('SELECT * from migrations');
        if ($result) {
            foreach ($result as $table) {
                if ($name == $table->migration) {
                    return;
                }
            }
        }

        DataBase::prepare('insert into migrations (migration,created_at) values (:name,:time)', [
            'name' => $name,
            'time' => $time
        ]);

        return true;
    }

    private function getMigrations()
    {
        $migrations = [];
        foreach (scandir('migrations') as $filename) {
            if (!in_array($filename, ['.', '..'])) {
                $filename = str_replace('.php', '', $filename);
                $migrations[] = $filename;
            }
        }

        return $migrations;
    }

    private function deleteMigrations()
    {
        return DataBase::prepare('DELETE FROM migrations');
    }
}

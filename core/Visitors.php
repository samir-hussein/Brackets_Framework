<?php

namespace App;

class Visitors
{
    public function __construct()
    {
        $this->createTables();
        $this->checkDateOfDay();
    }

    private function createTables()
    {
        DataBase::createTable('visits', [
            "total_visits BIGINT(20) DEFAULT '0' NOT NULL",
            "daily_visits BIGINT(20) DEFAULT '0' NOT NULL",
            "today INT(11) DEFAULT '0' NOT NULL"
        ]);
        $response = DataBase::countRows('visits');
        if ($response < 1) {
            $query = "INSERT INTO visits (total_visits,daily_visits,today) VALUES (0,0,0)";
            DataBase::prepare($query);
        }
        DataBase::createTable('visitors', [
            "id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY",
            "visitor_ip VARCHAR(255) NOT NULL"
        ]);
    }

    private function checkDateOfDay()
    {
        $sql = "SELECT today FROM visits";
        if ($response = DataBase::prepare($sql)) {
            foreach ($response as $row) {
                $today = $row->today;
            }
            $day = date('d');
            if ($day != $today) {
                $sql = "UPDATE visits SET today=$day, daily_visits=0";
                DataBase::prepare($sql);
            }
        }
    }

    public static function createNewVisitor()
    {
        $visitor_ip = $_SERVER['REMOTE_ADDR'];
        $sql = "SELECT * FROM visitors WHERE visitor_ip='$visitor_ip'";
        if (!DataBase::prepare($sql)) {
            $sql = "INSERT INTO visitors (visitor_ip) VALUES ('$visitor_ip')";
            DataBase::prepare($sql);
        }
    }

    public static function getTotalVisitors()
    {
        return DataBase::countRows('visitors');
    }

    public static function countTotalVisits()
    {
        return DataBase::increment('total_visits', 1, 'visits');
    }

    public static function getTotalVisits()
    {
        $sql = "SELECT total_visits FROM visits";
        if ($response = DataBase::prepare($sql)) {
            foreach ($response as $row) {
                return $row->total_visits;
            }
        }
    }

    public static function countDailyVisits()
    {
        return DataBase::increment('daily_visits', 1, 'visits');
    }

    public static function getDailyVisits()
    {
        $sql = "SELECT daily_visits FROM visits";
        if ($response = DataBase::prepare($sql)) {
            foreach ($response as $row) {
                return $row->daily_visits;
            }
        }
    }
}

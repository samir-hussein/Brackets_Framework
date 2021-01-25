<?php

namespace core;

class Auth
{

    public static function attempt($email, $password, $remember = null)
    {
        $sql = "SELECT * FROM users WHERE email=:email";
        $value = ['email' => $email];
        if ($result = DataBase::prepare($sql, $value)) {

            foreach ($result as $row) {
                if (password_verify($password, $row['password'])) {
                    Session::set('id', $row['id']);
                    Session::set('name', $row['name']);
                    Session::set('status', $row['status']);
                    Session::set('user', $row['email']);
                    if ($remember == true) {
                        setcookie('remember_user', $row['email'], time() + (86400 * 30));
                    }
                    return true;
                }
            }
        } else return false;
    }

    public static function user()
    {
        if (isset($_SESSION['user'])) {
            $user = [
                'email' => $_SESSION['user'],
                'name' => $_SESSION['name'],
                'id' => $_SESSION['id'],
                'status' => $_SESSION['status'],
            ];
            return json_decode(json_encode($user), FALSE);
        } else return null;
    }

    public static function id()
    {
        if (self::user() != null) {
            return self::user()->id;
        } else return null;
    }

    public static function check()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!isset($_COOKIE['remember_user'])) {
            if (isset($_SESSION['user'])) {
                return true;
            } else return false;
        } else return true;
    }

    public static function is_admin()
    {
        if (self::user() != null) {
            if (self::user()->status == 'admin') return true;
            else return false;
        } else return false;
    }

    public static function logout()
    {
        unset($_COOKIE['remember_user']);
        setcookie("remember_user", "", time() - (86400 * 30));
        Session::remove('user');
        Session::remove('name');
        Session::remove('id');
        Session::remove('status');
    }
}

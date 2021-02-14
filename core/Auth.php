<?php

namespace App;

class Auth
{

    public static function attempt(string $email, string $password, bool $remember = null)
    {
        $sql = "SELECT * FROM users WHERE email=:email";
        $value = ['email' => $email];
        if ($result = DataBase::prepare($sql, $value)) {
            if (password_verify($password, $result['password'])) {

                Session::set('id', $result['id']);
                Session::set('name', $result['name']);
                Session::set('status', $result['status']);
                Session::set('user', $result['email']);
                if ($remember == true) {
                    Cookies::set('remember_user', $result['email'], (86400 * 30));
                    Cookies::set('id', $result['id'], (86400 * 30));
                    Cookies::set('name', $result['name'], (86400 * 30));
                    Cookies::set('status', $result['status'], (86400 * 30));
                }
                return true;
            } else {
                return 'password is incorrect';
            }
        } else {
            return 'user not found';
        }
    }

    public static function user()
    {
        if (!is_null(Session::get('user'))) {
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
        if (is_null(Cookies::get('remember_user'))) {
            if (!is_null(Session::get('user'))) {
                return true;
            } else return false;
        } else {
            Session::set('id', Cookies::get('id'));
            Session::set('name', Cookies::get('name'));
            Session::set('status', Cookies::get('status'));
            Session::set('user', Cookies::get('remember_user'));
            return true;
        }
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
        Cookies::remove('remember_user');
        Cookies::remove('id');
        Cookies::remove('name');
        Cookies::remove('status');
        Session::remove('user');
        Session::remove('name');
        Session::remove('id');
        Session::remove('status');
    }
}

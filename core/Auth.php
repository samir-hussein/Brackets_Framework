<?php

namespace App;

use App\Database\DataBase;

class Auth
{
    private static $table;

    public static function guard(string $name)
    {
        self::$table = $name ?? null;
        return new Auth;
    }

    public static function attempt(string $email, string $password, bool $remember = null)
    {
        $table = self::$table ?? 'users';
        $sql = "SELECT * FROM $table WHERE email=:email";
        $value = ['email' => $email];
        if ($result = DataBase::prepare($sql, $value)) {
            foreach ($result as $row) {
                if (password_verify($password, $row->password)) {
                    $row = (array) $row;
                    unset($row['password']);
                    Session::set('user', array_keys($row));
                    foreach ($row as $key => $value) {
                        if ($key != 'password') {
                            Session::set($key, $value);
                        }
                    }
                    if ($remember == true) {
                        foreach ($row as $key => $value) {
                            if ($key != 'password') {
                                Cookies::set($key, $value, (86400 * 30));
                            }
                        }
                        Cookies::set('remember_user', json_encode($row), (86400 * 30));
                    }
                    return true;
                } else {
                    return 'password is incorrect';
                }
            }
        } else {
            return 'user not found';
        }
    }

    public static function user()
    {
        if (!is_null(Session::get('user'))) {
            $user = [];
            foreach (Session::get('user') as $value) {
                $user[$value] = $_SESSION[$value];
            }
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
        if (is_null(Session::get('user'))) {
            if (is_null(Cookies::get('remember_user'))) {
                return false;
            } else {
                $user = array_keys(json_decode(Cookies::get('remember_user'), true));
                foreach ($user as $value) {
                    Session::set($value, Cookies::get($value));
                }
                Session::set('user', $user);
                return true;
            }
        } else {
            return true;
        }
    }

    public static function is_admin()
    {
        if (self::user() != null) {
            if (self::user()->role == 'admin') return true;
            else return false;
        } else return false;
    }

    public static function is_verified()
    {
        if (self::user() != null) {
            if (is_null(self::user()->email_verifed_at)) return false;
            else return true;
        } else return null;
    }

    public static function logout()
    {
        foreach (Session::get('user') as $value) {
            Cookies::remove($value);
            Session::remove($value);
        }
        Cookies::remove('remember_user');
        Session::remove('user');
    }
}

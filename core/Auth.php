<?php

namespace App;

use App\Database\DataBase;

class Auth
{
    private static $table;
    private static $remember;

    public static function guard(string $name)
    {
        $guard = fetchFile('../config/auth.php')['guards'][$name];
        self::$table = $guard['table'];
        self::$remember = $guard['remember'] ?? (60 * 60 * 24 * 30);
        return new Auth;
    }

    public static function attempt(array $userInfo, bool $remember = null)
    {
        $table = self::$table;
        if (!self::$table) {
            $defaults = fetchFile('../config/auth.php')['defaults']['guard'];
            $guard = fetchFile('../config/auth.php')['guards'][$defaults];
            self::$table = $guard['table'];
            self::$remember = $guard['remember'] ?? (60 * 60 * 24 * 30);
        }

        $sql = "SELECT * FROM $table WHERE email=:email";
        $value = ['email' => $userInfo['email']];
        if ($result = DataBase::prepare($sql, $value)) {
            foreach ($result as $row) {
                if (password_verify($userInfo['password'], $row->password)) {
                    $row = (array) $row;
                    unset($row['password']);
                    Session::put('user', array_keys($row));
                    foreach ($row as $key => $value) {
                        if ($key != 'password') {
                            Session::put($key, $value);
                        }
                    }
                    if ($remember == true) {
                        foreach ($row as $key => $value) {
                            if ($key != 'password') {
                                Cookies::set($key, $value, (self::$remember));
                            }
                        }
                        Cookies::set('remember_user', json_encode($row), (self::$remember));
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
                    Session::put($value, Cookies::get($value));
                }
                Session::put('user', $user);
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

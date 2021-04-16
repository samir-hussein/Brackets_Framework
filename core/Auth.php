<?php

namespace App;

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
                    Session::set('id', $row->id);
                    Session::set('name', $row->name);
                    Session::set('role', $row->role ?? null);
                    Session::set('email', $row->email);
                    Session::set('user', $row->email);
                    Session::set('image', $row->image);
                    if ($remember == true) {
                        Cookies::set('remember_user', $row->email, (86400 * 30));
                        Cookies::set('id', $row->id, (86400 * 30));
                        Cookies::set('name', $row->name, (86400 * 30));
                        Cookies::set('email', $row->email, (86400 * 30));
                        Cookies::set('image', $row->image, (86400 * 30));
                        Cookies::set('role', $row->role, (86400 * 30));
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
            $user = [
                'email' => $_SESSION['email'],
                'image' => $_SESSION['image'],
                'name' => $_SESSION['name'],
                'id' => $_SESSION['id'],
                'role' => $_SESSION['role'],
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
            Session::set('image', Cookies::get('image'));
            Session::set('name', Cookies::get('name'));
            Session::set('email', Cookies::get('email'));
            Session::set('role', Cookies::get('role'));
            Session::set('user', Cookies::get('remember_user'));
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

    public static function logout()
    {
        Cookies::remove('remember_user');
        Cookies::remove('id');
        Cookies::remove('name');
        Cookies::remove('image');
        Cookies::remove('role');
        Cookies::remove('email');
        Session::remove('email');
        Session::remove('user');
        Session::remove('image');
        Session::remove('name');
        Session::remove('id');
        Session::remove('role');
    }
}

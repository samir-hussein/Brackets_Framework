<?php

namespace App\ResetPassword;

use App\Mail;
use App\Database\DataBase;
use Models\ResetPassword;
use Models\UserModel;

class PasswordReset
{
    private static $table = null;

    public function table(string $table)
    {
        self::$table = $table;
    }

    public static function sendResetLink(string $email_verifed, callable $callback = null): bool
    {
        global $email;
        $email = $email_verifed;
        if (!is_null($callback)) {
            call_user_func($callback, new PasswordReset);
        }

        $table = (self::$table) ?? 'users';

        $sql = "SELECT email FROM $table WHERE email=:email";
        $value = ['email' => $email];
        $user = DataBase::prepare($sql, $value);

        if (!is_null($user)) {
            $token = token();
            $expire = strtotime('+1 hour', time());

            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $href = "$protocol://" . $_SERVER['HTTP_HOST'] . "/reset-password/$token";

            $data = [
                'href' => $href
            ];

            $check = ResetPassword::where(['email', '=', $email])->get();

            if (is_null($check)) {
                ResetPassword::insert([
                    'email' => $email,
                    'token' => $token,
                    'expire_at' => $expire
                ]);
            } else {
                ResetPassword::where(['email', '=', $email])->update([
                    'token' => $token,
                    'expire_at' => $expire
                ]);
            }

            Mail::send(function ($message) {
                global $email;
                $message->from(config('MAIL_USERNAME'), config('APP_NAME'));
                $message->to($email);
                $message->subject('Email Verification');
            }, '../core/ResetPassword/password_view', $data);

            return true;
        } else {
            return false;
        }
    }

    public static function reset(string $email, string $token, string $new_password, string $confirm_new_password)
    {
        $check = ResetPassword::where(['email', '=', $email])->get()[0];

        if ($check) {
            if ($check->token == $token && $check->expire_at >= time()) {
                if ($new_password == $confirm_new_password) {
                    UserModel::where(['email', '=', $email])->update([
                        'password' => password_hash($new_password, PASSWORD_DEFAULT)
                    ]);
                    ResetPassword::where(['email', '=', $email])->delete();
                    return redirect('/login', ['status' => 'Password has been changed successfully']);
                } else {
                    return false;
                }
            } else {
                return redirect("/request-reset-password");
            }
        } else {
            return redirect('/login');
        }
    }
}

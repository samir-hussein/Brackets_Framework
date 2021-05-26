<?php

namespace App\Email_Verification;

use App\Mail;
use App\Database\DataBase;
use Models\EmailVerification;
use Models\UserModel;

class VerifyEmail
{
    private static $table = null;
    private static $password = null;

    public function password(string $password)
    {
        self::$password = $password;
    }

    public function table(string $table)
    {
        self::$table = $table;
    }

    public static function send_verify(string $email_verifed, callable $callback = null): bool
    {
        global $email;
        $email = $email_verifed;
        if (!is_null($callback)) {
            call_user_func($callback, new VerifyEmail);
        }

        $table = (self::$table) ?? 'users';

        $sql = "SELECT email FROM $table WHERE email=:email";
        $value = ['email' => $email];
        $user = DataBase::prepare($sql, $value);

        if (!is_null($user)) {
            $password = (self::$password) ?? null;
            $token = token();
            $expire = strtotime('+1 hour', time());

            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $href = "$protocol://" . $_SERVER['HTTP_HOST'] . "/verify-email?email=$email&token=$token";

            $data = [
                'password' => $password,
                'href' => $href
            ];

            $check = EmailVerification::where(['email', '=', $email])->get();

            if (is_null($check)) {
                EmailVerification::insert([
                    'email' => $email,
                    'token' => $token,
                    'expire_at' => $expire
                ]);
            } else {
                EmailVerification::where(['email', '=', $email])->update([
                    'token' => $token,
                    'expire_at' => $expire
                ]);
            }

            Mail::send(function ($message) {
                global $email;
                $message->from(config('Mail', 'MAIL_USERNAME'), config('App', 'APP_NAME'));
                $message->to($email);
                $message->subject('Email Verification');
            }, '../core/Email_Verification/email_view', $data);

            return true;
        } else {
            return false;
        }
    }

    public static function verify(string $email, string $token)
    {
        $check = EmailVerification::where(['email', '=', $email])->get()[0];

        if ($check) {
            if ($check->token == $token && $check->expire_at >= time()) {
                UserModel::where(['email', '=', $email])->update([
                    'email_verifed_at' => date('Y-m-d H:i:s')
                ]);
                EmailVerification::where(['email', '=', $email])->delete();
                return redirect('/login', ['status' => 'Email has been verified successfully']);
            } else {
                return redirect("/request-verify-email");
            }
        } else {
            return redirect('/login');
        }
    }
}

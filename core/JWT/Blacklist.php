<?php

namespace App\JWT;

class Blacklist
{
    public static function add(string $token)
    {
        if (JWT::tokenValidate($token)) {
            $tokens = self::get();
            if (!in_array($token, $tokens)) {
                array_push($tokens, $token);
                file_put_contents('../core/JWT/black_list.php', "<?php\nreturn " . var_export($tokens, true) . ";");
            }
        }
    }

    public static function get()
    {
        return fetchFile('JWT/black_list.php');
    }

    public static function check(string $token)
    {
        $tokens = self::get();
        if (in_array($token, $tokens)) {
            return true;
        }
        return false;
    }
}

<?php

namespace App\JWT;

class Validate
{
    public static function isJWTValid($token)
    {
        if (!$token) {
            return false;
        }

        // split the jwt
        $tokenParts = explode('.', $token);
        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $signature_provided = $tokenParts[2];

        $signature = Signature::makeSignature($header, $payload);

        $payload = base64_decode($payload);

        // check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
        $expiration = json_decode($payload)->exp;
        $is_token_expired = ($expiration - time()) < 0;


        // verify it matches the signature provided in the jwt
        $is_signature_valid = ($signature === $signature_provided);

        if ($is_token_expired || !$is_signature_valid || Blacklist::check($token)) {
            if ($is_token_expired && Blacklist::check($token)) {
                $tokens = Blacklist::get();
                foreach ($tokens as $key => $val) {
                    if ($token == $val) {
                        unset($tokens[$key]);
                        file_put_contents('../core/JWT/black_list.php', "<?php\nreturn " . var_export($tokens, true) . ";");
                        break;
                    }
                }
            }

            return false;
        } else {
            return true;
        }
    }
}

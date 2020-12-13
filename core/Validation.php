<?php

namespace core;

class Validation
{

    public static function validateInput($input)
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }

    public static function email($email)
    {
        $email = self::validateInput($email);
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) return true;
        else return false;
    }

    public static function url($url)
    {
        $url = self::validateInput($url);
        if(filter_var($url, FILTER_VALIDATE_URL)) return true;
        else return false;
    }

    public static function phoneNumber($number)
    {
        return filter_var($number,FILTER_SANITIZE_NUMBER_INT);
    }

    public static function password($password)
    {
        // must be more than 8 ASCII characters, contain at least one upper case letter, one lower case letter and one digit.

        $pattern='/\A(?=[\x20-\x7E]*?[A-Z])(?=[\x20-\x7E]*?[a-z])(?=[\x20-\x7E]*?[0-9])[\x20-\x7E]{8,}\z/';

        if(!preg_match($pattern,$password))
        {
            return false;
        }
        else return self::validateInput($password);
    }

    public static function file(string $fileName,array $allowedExt,string $dir,string $prefix = '')
    {
        /* 
            extenstion array must be in lowercase
            $prefix argument is optional
            error 1 => file name is empty
            error 2 => invalid extension
            error 3 => file not moved
        */
        if(!empty($_FILES[$fileName]['name'])){
            $file = $_FILES[$fileName]['name'];
            $ext = explode('.',$file);
            $ext = strtolower(end($ext));
            if(in_array($ext,$allowedExt))
            {
                $newDir = $dir;
                $newName = uniqid(($prefix !== "")? $prefix:"") . "." . $ext;
                $target = $newDir . $newName;
                if(move_uploaded_file($_FILES[$fileName]['tmp_name'],$target))
                {
                    $response = [
                        'name' => $newName,
                        'ext' => $ext
                    ];
                    return $response;
                }
                else return 3;
            }
            else return 2;
        }
        else return 1;
    }

    public static function files($fileName,$allowedExt,$dir,$prefix = '')
    {
        /* 
            extenstion array must be in lowercase
            $prefix argument is optional
            error 1 => file name is empty
            error 2 => invalid extension
            error 3 => file not moved
        */
        $images = [];
        for($i= 0; $i < count($_FILES[$fileName]['name']); $i++){
            if(!empty($_FILES[$fileName]['name'][$i])){
                $file = $_FILES[$fileName]['name'][$i];
                $ext = explode('.',$file);
                $ext = strtolower(end($ext));
                if(in_array($ext,$allowedExt))
                {
                    $newDir = $dir;
                    $newName = uniqid(($prefix !== "")? $prefix:"") . "." . $ext;
                    $target = $newDir . $newName;
                    if(move_uploaded_file($_FILES[$fileName]['tmp_name'][$i],$target))
                    {
                        $response = [
                            'name' => $newName,
                            'ext' => $ext
                        ];
                        $images[] = $response;
                    }
                    else return 3;
                }
                else return 2;
            }
            else return 1;   
        }
        return $images;
    }

    public static function convertpngToWebp($filename,$dir = null){
        $img = imagecreatefrompng($filename);
        imagepalettetotruecolor($img);
        $target = $dir . str_ireplace('png','webp',$filename);
        $webp = imagewebp($img,$target);
        return $webp;
    }

    public static function convertjpgToWebp($filename,$dir = null){
        $image = imagecreatefromstring(file_get_contents($filename));
        ob_start();
        imagejpeg($image,null,100);
        $cont = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        $content = imagecreatefromstring($cont);
        $target = $dir . str_ireplace('jpg','webp',$filename);
        $webp = imagewebp($content,$target);
        imagedestroy($content);
        return $webp;
    }

    public static function convertjpegToWebp($filename,$dir = null){
        $image = imagecreatefromstring(file_get_contents($filename));
        ob_start();
        imagejpeg($image,null,100);
        $cont = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        $content = imagecreatefromstring($cont);
        $target = $dir . str_ireplace('jpeg','webp',$filename);
        $webp = imagewebp($content,$target);
        imagedestroy($content);
        return $webp;
    }
}


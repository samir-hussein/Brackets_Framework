<?php

namespace App;

class Validation
{

    public static function validateInput($input)
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }

    public static function validateArray($array)
    {
        for ($i = 0; $i < count($array); $i++) {
            if (!is_array($array[$i])) {
                $array[$i] = trim($array[$i]);
                $array[$i] = stripslashes($array[$i]);
                $array[$i] = htmlspecialchars($array[$i]);
            } else {
                $array[$i] = self::validateArray($array[$i]);
            }
        }
        return $array;
    }

    public static function validEmail($email)
    {
        $email = self::validateInput($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        } else {
            return false;
        }
    }

    public static function url($url)
    {
        $url = self::validateInput($url);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        } else {
            return false;
        }
    }

    public static function number($number)
    {
        return filter_var($number, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function money($number)
    {
        return filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function password($password)
    {
        // must be more than 8 ASCII characters, contain at least one upper case letter, one lower case letter,one spspecial character of (@#$&*) and one digit.

        $pattern = '/(?=[\x20-\x7E]*?[A-Z])(?=[\x20-\x7E]*?[a-z])(?=[\x20-\x7E]*?[0-9])(?=[\x20-\x7E]*?[@#$&*])[\x20-\x7E]{8,}/';

        if (!preg_match($pattern, $password)) {
            return false;
        } else {
            return self::validateInput($password);
        }
    }

    public static function file(string $fileName, array $allowedExt, string $dir, string $prefix = '')
    {
        /*
        extenstion array must be in lowercase
        $prefix argument is optional
        error 1 => file name is empty or error upload
        error 2 => invalid extension
        error 3 => file not moved
         */
        if (!empty($_FILES[$fileName]['name']) || $_FILES[$fileName]['error'] == 1) {
            $ext = explode('/', $_FILES[$fileName]['type']);
            $ext = strtolower(end($ext));
            if (in_array($ext, $allowedExt)) {
                $newDir = $dir;
                $newName = uniqid(($prefix !== "") ? $prefix : "") . "." . $ext;
                $target = $newDir . $newName;
                if (move_uploaded_file($_FILES[$fileName]['tmp_name'], $target)) {
                    $response = [
                        'name' => $newName,
                        'ext' => $ext,
                    ];
                    return $response;
                } else {
                    return 3;
                }
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    }

    public static function files($fileName, $fileSize, $allowedExt, $dir, $prefix = '')
    {
        /*
        fileSize param in bytes
        extenstion array must be in lowercase
        $prefix argument is optional
        error 1 => file name is empty
        error 2 => invalid extension
        error 3 => file not moved
        error 4 => file size too large
         */
        $images = [];
        for ($i = 0; $i < count($_FILES[$fileName]['name']); $i++) {
            if (!empty($_FILES[$fileName]['name'][$i])) {
                $ext = explode('/', $_FILES[$fileName]['type'][$i]);
                $ext = strtolower(end($ext));
                if ($_FILES[$fileName]['size'][$i] <= $fileSize) {
                    if (in_array($ext, $allowedExt)) {
                        $newDir = $dir;
                        $newName = uniqid(($prefix !== "") ? $prefix : "") . "." . $ext;
                        $target = $newDir . $newName;
                        if (move_uploaded_file($_FILES[$fileName]['tmp_name'][$i], $target)) {
                            $response = [
                                'name' => $newName,
                                'ext' => $ext,
                            ];
                            $images[] = $response;
                        } else {
                            return 3;
                        }
                    } else {
                        return 2;
                    }
                } else {
                    return 4;
                }
            } else {
                return 1;
            }
        }
        return $images;
    }

    public static function convertpngToWebp($filename, $dir = null)
    {
        $img = imagecreatefrompng($filename);
        imagepalettetotruecolor($img);
        $target = $dir . str_ireplace('png', 'webp', $filename);
        $webp = imagewebp($img, $target);
        return $webp;
    }

    public static function convertjpgToWebp($filename, $dir = null)
    {
        $image = imagecreatefromstring(file_get_contents($filename));
        ob_start();
        imagejpeg($image, null, 100);
        $cont = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        $content = imagecreatefromstring($cont);
        $target = $dir . str_ireplace('jpg', 'webp', $filename);
        $webp = imagewebp($content, $target);
        imagedestroy($content);
        return $webp;
    }

    public static function convertjpegToWebp($filename, $dir = null)
    {
        $image = imagecreatefromstring(file_get_contents($filename));
        ob_start();
        imagejpeg($image, null, 100);
        $cont = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        $content = imagecreatefromstring($cont);
        $target = $dir . str_ireplace('jpeg', 'webp', $filename);
        $webp = imagewebp($content, $target);
        imagedestroy($content);
        return $webp;
    }
}

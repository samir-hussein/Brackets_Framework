<?php

namespace App;

use DateTime;

class Validatore
{
    private static $errors = [];
    private static $params = [];
    private static $oldValues = [];

    private static function required($input)
    {
        $input = $_REQUEST[$input];
        if (!is_array($input)) $input = trim($input);
        if (isset($input) && !empty($input)) return true;
        else return false;
    }

    private static function unique($input, $model)
    {
        $column = $input;
        $input = $_REQUEST[$input];
        $model = "Models\\" . $model;
        $response = $model::where([$column, '=', $input])->get();
        if ($response) return false;
        else return true;
    }

    private static function image($input)
    {
        $mime = $_FILES[$input]['type'];
        if (strstr($mime, "image/")) return true;
        return false;
    }

    private static function video($input)
    {
        $mime = $_FILES[$input]['type'];
        if (strstr($mime, "video/")) return true;
        return false;
    }

    private static function email($input)
    {
        $input = $_REQUEST[$input];
        if (Validation::validEmail($input)) return true;
        else return false;
    }

    private static function same($input, $confirmed)
    {
        $input = $_REQUEST[$input];
        $confirmed = $_REQUEST[$confirmed];
        if ($input == $confirmed) return true;
        else return false;
    }

    private static function size($input, $size)
    {
        $input = $_REQUEST[$input];
        if (is_array($input)) {
            if (count($input) == $size) return true;
            else return false;
        } elseif (is_string($input)) {
            $input = trim($input);
            if (strlen($input) == $size) return true;
            else return false;
        }
    }

    private static function max($input, $max)
    {
        $input = $_REQUEST[$input];
        if (is_array($input)) {
            if (count($input) <= $max) return true;
            else return false;
        } elseif (is_string($input)) {
            $input = trim($input);
            if (strlen($input) <= $max) return true;
            else return false;
        } elseif (is_numeric($input)) {
            if ($input <= $max) return true;
            else return false;
        }
    }

    private static function num($input)
    {
        $input = $_REQUEST[$input];
        if (is_numeric($input)) return true;
        else return false;
    }

    private static function min($input, $min)
    {
        $input = $_REQUEST[$input];
        if (is_array($input)) {
            if (count($input) >= $min) return true;
            else return false;
        } elseif (is_string($input)) {
            $input = trim($input);
            if (strlen($input) >= $min) return true;
            else return false;
        } elseif (is_numeric($input)) {
            if ($input >= $min) return true;
            else return false;
        }
    }

    private static function date($input, $format = null)
    {
        $input = $_REQUEST[$input];
        if ($format == null) $format = 'Y-m-d';
        $date = DateTime::createFromFormat($format, $input);
        if ($date !== false) {
            if ($date->format($format) === $input) return true;
            else return false;
        } else return false;
    }

    private static function integer($input)
    {
        $input = $_REQUEST[$input];
        if (is_int($input)) return true;
        else return false;
    }

    private static function mime($input, $types)
    {
        $types = explode(",", $types);
        $file = $_FILES[$input]['name'];
        $ext = explode('.', $file);
        $ext = strtolower(end($ext));
        if (in_array($ext, $types)) return true;
        else return false;
    }

    public static function make($inputs)
    {
        $flag = true;
        foreach ($inputs as $key => $values) {
            if (isset($_REQUEST[$key]))
                self::$oldValues[$key] = $_REQUEST[$key];

            $values = explode('|', $values);
            foreach ($values as $value) {
                $fun = $value;
                if (strpos($value, ':') !== false) {
                    $options = explode(':', $value);
                    $fun =  $options[0];
                    $param =  $options[1];
                }
                if (!self::$fun($key, (isset($param)) ? $param : null)) {
                    if (!array_key_exists($key, self::$errors)) {
                        $flag = false;
                        if (isset($param)) self::$params[$key] = $param;
                        if ($fun == "unique") {
                            self::$params[$key] = $key;
                        }
                        self::$errors[$key] = $fun;
                    }
                }
            }
        }

        if ($flag) return true;
        else return false;
    }

    private static function message()
    {
        return [
            'required' => 'this is required',
            'email' => 'invalid email address',
            'size' => 'max size is {{val}}',
            'max' => 'maximum is {{val}}',
            'min' => 'minimum is {{val}}',
            'num' => 'this not number',
            'integer' => 'this is not integer value',
            'same' => 'this is not match {{val}}',
            'date' => 'invalid date format',
            'mime' => 'file extension must be {{val}}',
            'image' => 'image is required',
            'video' => 'video is required',
            'unique' => 'this {{val}} is already exist'
        ];
    }

    public static function errors()
    {
        $params = self::$params;
        $errors = self::$errors;
        $arr = [];

        foreach ($errors as $key => $value) {
            $val = self::message()[$value];
            if (strpos($val, '{{val}}') !== false) {
                $val = str_replace('{{val}}', $params[$key], $val);
            }
            $arr[$key] = $val;
        }

        return $arr;
    }

    public static function old()
    {
        $response = self::$oldValues;
        return json_decode(json_encode($response), false);
    }
}

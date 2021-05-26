<?php

namespace App;

class Http
{
    private static $headers;

    public static function headers(array $headers)
    {
        self::$headers = $headers;
        return new Http;
    }

    private static function request(string $method, string $url, array $data = null)
    {
        $data = json_encode($data);
        $ch = curl_init();
        $headerArray = array(
            "Content-Type: application/json",
            "Accept: application/json",
        );
        $headerArray = array_merge($headerArray, self::$headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        $response = curl_exec($ch);
        if ($response === false) {
            echo curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }

    public static function get(string $url)
    {
        return self::request('GET', $url);
    }

    public static function post(string $url, array $data)
    {
        return self::request('POST', $url, $data);
    }

    public static function put(string $url, array $data)
    {
        return self::request('PUT', $url, $data);
    }

    public static function delete(string $url)
    {
        return self::request(strtoupper('delete'), $url);
    }
}

<?php

namespace App;

class TwoCheckOut
{
    private static $merchantCode;
    private static $privateKey;
    private static $demo;

    public function __construct($config)
    {
        self::$merchantCode = $config['merchantCode'];
        self::$privateKey = $config['privateKey'];
        self::$demo = $config['demo'];
    }

    public static function createSale(array $requestData)
    {
        $host = "https://www.2checkout.com/checkout/api/1/" . self::$merchantCode . "/rs/authService";
        $requestData['sellerId'] = self::$merchantCode;
        $requestData['privateKey'] = self::$privateKey;
        $requestData['demo'] = self::$demo;
        $payload = json_encode($requestData);
        $ch = curl_init();
        $headerArray = array(
            "Content-Type: application/json",
            "Accept: application/json",
        );
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        $response = curl_exec($ch);
        if ($response === false) {
            echo curl_error($ch);
        }
        curl_close($ch);
        return json_decode($response);
    }

    // example for requestData array required
    // $requestData = [
    //     "merchantOrderId" => "123",
    //     "currency" => "EGP",
    //     "token" => $_POST['token'],
    //     "lineItems" => [
    //         [
    //             "name" => "John Doe",
    //             "price" => "4.99",
    //             "type" => "product",
    //             "quantity" => "1",
    //             "recurrence" => "4 Year",
    //             "startupFee" => "0"
    //         ]
    //     ],
    //     "billingAddr" => [
    //         "name" => "John Doe",
    //         "addrLine1" => "123 test blvd",
    //         "city" => "nasr city", // nasr City
    //         "state" => "cairo", //cairo
    //         "zipCode" => "43123",
    //         "country" => "EGY", // EGY
    //         "email" => "example@2co.com",
    //         "phoneNumber" => "123456789"
    //     ],
    // ];
}

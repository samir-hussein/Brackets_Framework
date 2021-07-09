<?php

namespace Controllers;

use App\PayMob;

class PayMobController
{
    public function index()
    {
        $auth = PayMob::AuthenticationRequest();

        $order = PayMob::OrderRegistrationAPI([
            'auth_token' => $auth->token,
            'amount_cents' => 150 * 100, //put your price
            'currency' => 'EGP',
            'delivery_needed' => false,
            'merchant_order_id' => 5, //put order id from your database
            'items' => [[ // all items information
                "name" => "ASC1515",
                "amount_cents" => 150 * 100,
                "description" => "Smart Watch",
                "quantity" => "2"
            ]]
        ]);

        $PaymentKey = PayMob::PaymentKeyRequest([
            'auth_token' => $auth->token,
            'amount_cents' => 150 * 100,
            'currency' => 'EGP',
            'order_id' => $order->id,
            "billing_data" => [ // put your client information
                "apartment" => "803",
                "email" => "claudette09@exa.com",
                "floor" => "42",
                "first_name" => "Clifford",
                "street" => "Ethan Land",
                "building" => "8028",
                "phone_number" => "+86(8)9135210487",
                "shipping_method" => "PKG",
                "postal_code" => "01898",
                "city" => "Jaskolskiburgh",
                "country" => "CR",
                "last_name" => "Nicolas",
                "state" => "Utah"
            ]
        ]);

        // render view page card information here
?>

        <iframe width="100%" height="700" src="https://accept.paymob.com/api/acceptance/iframes/165948?payment_token=<?= $PaymentKey->token ?>">
    <?php
    }
}

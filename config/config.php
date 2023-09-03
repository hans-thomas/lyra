<?php

use Hans\Lyra\Gateways\Payir;
use Hans\Lyra\Gateways\Zarinpal;

return [
    'default'  => Payir::class,
    'gateways' => [
        Zarinpal::class => [
            'mode' => 'normal',

            'merchant_id'  => '',
            'callback_url' => 'https://www.yoursite.com/verify.php',
            'description'  => '',
            'metadata'     => [
                'email'  => 'info@email.com',
                'mobile' => '09121234567',
            ],

            'modes' => [
                'normal'    => [
                    'purchase'     => 'https://api.zarinpal.com/pg/v4/payment/request.json',
                    'payment'      => 'https://www.zarinpal.com/pg/StartPay/:authority',
                    'verification' => 'https://api.zarinpal.com/pg/v4/payment/verify.json',
                ],
                'sandbox'   => [
                    'purchase'     => 'https://sandbox.zarinpal.com/pg/v4/payment/request.json',
                    'payment'      => 'https://sandbox.zarinpal.com/pg/StartPay/:authority',
                    'verification' => 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json',
                ],
                'zaringate' => [
                    'purchase'     => 'https://api.zarinpal.com/pg/v4/payment/request.json',
                    'payment'      => 'https://www.zarinpal.com/pg/StartPay/:authority/ZarinGate',
                    'verification' => 'https://api.zarinpal.com/pg/v4/payment/verify.json',
                ],
            ],

        ],
        Payir::class    => [
            'mode' => 'normal',

            'api'             => '',
            'amount'          => '',
            'redirect'        => 'https://Your-CallBack-URL',
            // optional parameters:
            'mobile'          => '',
            'factorNumber'    => '',
            'description'     => '',
            'validCardNumber' => '',

            'modes' => [
                'normal'  => [
                    'purchase'     => 'https://pay.ir/pg/send',
                    'payment'      => 'https://pay.ir/pg/:token',
                    'verification' => 'https://pay.ir/pg/verify',
                ],
                'sandbox' => [
                    'purchase'     => 'https://pay.ir/pg/send',
                    'payment'      => 'https://pay.ir/pg/:token',
                    'verification' => 'https://pay.ir/pg/verify',
                ],
            ],
        ],
    ],
];

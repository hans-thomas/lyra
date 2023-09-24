<?php

use Hans\Lyra\Gateways\IDPay;
use Hans\Lyra\Gateways\Payir;

return [
    'gateways' => [
        'default'       => Payir::class,
        Payir::class    => [
            'mode' => 'normal',

            'api'             => '',
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
        IDPay::class    => [
            'mode' => 'normal',

            'X-API-KEY' => '',
            'phone'     => '',
            'name'      => '',
            'desc'      => '',
            'callback'  => 'https://Your-CallBack-URL',

            'modes' => [
                'normal'  => [
                    'purchase'     => 'https://api.idpay.ir/v1.1/payment',
                    'payment'      => 'https://idpay.ir/p/ws/:id',
                    'verification' => 'https://api.idpay.ir/v1.1/payment/verify',
                ],
                'sandbox' => [
                    'purchase'     => 'https://api.idpay.ir/v1.1/payment',
                    'payment'      => 'https://idpay.ir/p/ws-sandbox/:id',
                    'verification' => 'https://api.idpay.ir/v1.1/payment/verify',
                ],
            ],
        ],
    ],
];

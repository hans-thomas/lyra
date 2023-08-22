<?php

	use Hans\Lyra\Gateways\Zarinpal;

	return [
		'default'  => Zarinpal::class,
		'gateways' => [
			Zarinpal::class => [
				'mode' => 'api',

				'merchant_id'  => '',
				'callback_url' => '',
				"email"        => '',
				"mobile"       => '',

				'modes' => [
					'api'       => [
						'apiPurchaseUrl'     => 'https://api.zarinpal.com/pg/v4/payment/request.json',
						'apiPaymentUrl'      => 'https://www.zarinpal.com/pg/StartPay/',
						'apiVerificationUrl' => 'https://api.zarinpal.com/pg/v4/payment/verify.json',
					],
					'sandbox'   => [
						'apiPurchaseUrl'     => 'https://sandbox.zarinpal.com/pg/services/WebGate/wsdl',
						'apiPaymentUrl'      => 'https://sandbox.zarinpal.com/pg/StartPay/',
						'apiVerificationUrl' => 'https://sandbox.zarinpal.com/pg/services/WebGate/wsdl',
					],
					'zaringate' => [
						'apiPurchaseUrl'     => 'https://ir.zarinpal.com/pg/services/WebGate/wsdl',
						'apiPaymentUrl'      => 'https://www.zarinpal.com/pg/StartPay/:authority/ZarinGate',
						'apiVerificationUrl' => 'https://ir.zarinpal.com/pg/services/WebGate/wsdl',
					]
				],

			]
		]
	];

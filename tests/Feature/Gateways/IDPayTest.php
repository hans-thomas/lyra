<?php

namespace Hans\Lyra\Tests\Feature\Gateways;

use Hans\Lyra\Gateways\IDPay;
use Hans\Lyra\Tests\TestCase;

class IDPayTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function request(): void
    {
        $instance = new IDPay(10000, 'sandbox');
        $orderId = generate_unique_invoice_number();
        self::assertIsString($instance->request($orderId));
    }
}
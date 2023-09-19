<?php

namespace Hans\Lyra\Tests\Feature\Gateways;

use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Facades\Lyra;
use Hans\Lyra\Gateways\IDPay;
use Hans\Lyra\Tests\Feature\Gateways\Contracts\Gateway;
use Hans\Lyra\Tests\TestCase;
use Illuminate\Support\Str;

class IDPayTest extends TestCase implements Gateway
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

    /**
     * @test
     *
     * @return void
     */
    public function requestWithInvalidSettings(): void
    {
        $instance = new IDPay(1, 'sandbox');
        $orderId = generate_unique_invoice_number();

        $this->expectExceptionMessage($instance->errorsList()[34]);
        self::assertIsString($instance->request($orderId));
    }

    /**
     * @test
     *
     * @return void
     */
    public function pay(): void
    {
        Lyra::setGateway(IDPay::class, 10000, 'sandbox');

        self::assertIsString($url = Lyra::pay()->getRedirectUrl());
        $response = $this->client->get($url)->getBody()->getContents();
        self::assertStringContainsString('در حال پردازش تراکنش ...', $response);
    }

    /**
     * @test
     *
     * @return void
     */
    public function verifyOnSuccess(): void
    {
        Lyra::setGateway(IDPay::class, 10000, 'sandbox');

        $url = Lyra::pay()->getRedirectUrl();
        $invoice = Lyra::getInvoice();
        $id = Str::afterLast($url, '/');
        request()->merge([
            'status'   => 100,
            'track_id' => rand(10, 99),
            'id'       => $id,
            'order_id' => $invoice->number,
        ]);

        self::assertNull($invoice->transaction_id);

        self::assertTrue(Lyra::verify());
        $invoice->refresh();

        self::assertIsString($invoice->transaction_id);
    }

    /**
     * @test
     *
     * @return void
     */
    public function verifyOnDuplicateVerification(): void
    {
        Lyra::setGateway(IDPay::class, 10000, 'sandbox');

        $url = Lyra::pay()->getRedirectUrl();
        $invoice = Lyra::getInvoice();
        $id = Str::afterLast($url, '/');
        request()->merge([
            'status'   => 100,
            'track_id' => rand(10, 99),
            'id'       => $id,
            'order_id' => $invoice->number,
        ]);

        self::assertTrue(Lyra::verify());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage((new $invoice->gateway())->errorsList()[53]);

        Lyra::verify();
    }

    /**
     * @test
     *
     * @return void
     */
    public function verifyOnFailed(): void
    {
        Lyra::setGateway(IDPay::class, 10000, 'sandbox');

        $url = Lyra::pay()->getRedirectUrl();
        $invoice = Lyra::getInvoice();
        $id = Str::afterLast($url, '/');
        request()->merge([
            'status'   => 1,
            'track_id' => rand(10, 99),
            'id'       => $id,
            'order_id' => $invoice->number,
        ]);

        $this->expectException(LyraException::class);
        $this->expectExceptionMessage("Verifying Invoice #{$invoice->number} failed!");

        Lyra::verify();
    }
}

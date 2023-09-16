<?php

namespace Hans\Lyra\Tests\Feature\Gateways;

use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Facades\Lyra;
use Hans\Lyra\Gateways\Payir;
use Hans\Lyra\Tests\Feature\Gateways\Contracts\Gateway;
use Hans\Lyra\Tests\TestCase;
use Illuminate\Support\Str;

class Payir extends TestCase implements Gateway
{
    /**
     * @test
     *
     * @return void
     */
    public function request(): void
    {
        Lyra::setGateway(Payir::class, 10000, 'sandbox');

        self::assertIsString(Lyra::pay()->getRedirectUrl());
    }

    /**
     * @test
     *
     * @return void
     */
    public function requestWithInvalidSettings(): void
    {
        Lyra::setGateway(Payir::class, 1, 'sandbox');
        $instance = new Payir();

        $this->expectExceptionMessage($instance->errorsList()[-12]);
        self::assertIsString(Lyra::pay());
    }

    /**
     * @test
     *
     * @return void
     */
    public function pay(): void
    {
        Lyra::setGateway(Payir::class, 10000, 'sandbox');

        self::assertIsString($url = Lyra::pay()->getRedirectUrl());
        $response = $this->client->get($url)->getBody()->getContents();
        self::assertStringContainsString('درگاه تست Pay.ir', $response);
    }

    /**
     * @test
     *
     * @return void
     */
    public function verifyOnSuccess(): void
    {
        Lyra::setGateway(Payir::class, 10000, 'sandbox');
        $invoice = Lyra::getInvoice();

        $url = Lyra::pay()->getRedirectUrl();
        $token = Str::afterLast($url, '/');
        request()->merge(['status' => 1, 'token' => $token]);

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
        Lyra::setGateway(Payir::class, 10000, 'sandbox');
        $invoice = Lyra::getInvoice();

        $url = Lyra::pay()->getRedirectUrl();
        $token = Str::afterLast($url, '/');
        request()->merge(['status' => 1, 'token' => $token]);

        self::assertTrue(Lyra::verify());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage((new $invoice->gateway())->errorsList()[-6]);

        Lyra::verify();
    }

    /**
     * @test
     *
     * @return void
     */
    public function verifyOnFailed(): void
    {
        Lyra::setGateway(Payir::class, 10000, 'sandbox');

        $url = Lyra::pay()->getRedirectUrl();
        $token = Str::afterLast($url, '/');
        request()->merge(['status' => 0, 'token' => $token]);

        $invoice = Lyra::getInvoice();

        $this->expectException(LyraException::class);
        $this->expectExceptionMessage("Verifying Invoice #{$invoice->number} failed!");

        Lyra::verify();
    }
}

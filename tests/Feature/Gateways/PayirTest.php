<?php

namespace Hans\Lyra\Tests\Feature\Gateways;

use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Facades\Lyra;
use Hans\Lyra\Gateways\Payir;
use Hans\Lyra\Tests\TestCase;
use Illuminate\Support\Str;

class PayirTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function request(): void
    {
        Lyra::setGateway(Payir::class, 10000, 'sandbox');

        self::assertIsString(Lyra::pay(1)->getRedirectUrl());
    }

    /**
     * @test
     *
     * @return void
     */
    public function requestWithInvalidSettings(): void
    {
        Lyra::setGateway(Payir::class, 1, 'sandbox');
        $instance = new Payir;

        $this->expectExceptionMessage($instance->errorsList()[-12]);
        self::assertIsString(Lyra::pay(1));
    }

    /**
     * @test
     *
     * @return void
     */
    public function pay(): void
    {
        Lyra::setGateway(Payir::class, 10000, 'sandbox');

        self::assertIsString($url = Lyra::pay(10000)->getRedirectUrl());
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

        $url = Lyra::pay(10000)->getRedirectUrl();
        $token = Str::afterLast($url, '/');
        request()->merge(['status' => 1, 'token' => $token]);

        self::assertTrue(Lyra::verify());
    }

    /**
     * @test
     *
     * @return void
     */
    public function verifyOnFailed(): void
    {
        Lyra::setGateway(Payir::class, 10000, 'sandbox');

        $url = Lyra::pay(10000)->getRedirectUrl();
        $token = Str::afterLast($url, '/');
        request()->merge(['status' => 0, 'token' => $token]);

        $invoice = Lyra::getInvoice();

        $this->expectException(LyraException::class);
        $this->expectExceptionMessage("Verifying Invoice #{$invoice->number} failed!");

        Lyra::verify();
    }
}

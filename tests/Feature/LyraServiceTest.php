<?php

namespace Hans\Lyra\Tests\Feature;

use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Facades\Lyra;
use Hans\Lyra\Gateways\IDPay;
use Hans\Lyra\Gateways\Payir;
use Hans\Lyra\Gateways\Zarinpal;
use Hans\Lyra\Helpers\Enums\Status;
use Hans\Lyra\LyraService;
use Hans\Lyra\Tests\TestCase;
use Illuminate\Http\RedirectResponse;

class LyraServiceTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function pay(): void
    {
        Lyra::pay(10000, 'sandbox');

        $invoice = Lyra::getInvoice();
        self::assertEquals(10000, $invoice->amount);
        self::assertStringEqualsStringIgnoringLineEndings(lyra_config('gateways.default'), $invoice->gateway);
        self::assertEquals(Status::PENDING, $invoice->status);
        self::assertNull($invoice->transaction_id);

        $url = Lyra::getRedirectUrl();
        self::assertStringContainsString($invoice->token, $url);
    }

    /**
     * @test
     *
     * @return void
     */
    public function getRedirectUrl(): void
    {
        $url = Lyra::pay(10000, 'sandbox')->getRedirectUrl();
        self::assertIsString($url);
        self::assertMatchesRegularExpression(
            '/http[[:alpha:]]?:\/\/(www\.)?(.+\.)?[[:alpha:]]+\.[[:alpha:]]+(\/.*)/i',
            $url
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function redirect(): void
    {
        $redirect = Lyra::pay(10000, 'sandbox')->redirect();

        self::assertInstanceOf(RedirectResponse::class, $redirect);
        self::assertEquals(Lyra::getRedirectUrl(), $redirect->getTargetUrl());
    }

    /**
     * @test
     *
     * @return void
     */
    public function setGateway(): void
    {
        Lyra::setGateway(Payir::class, 10000, 'sandbox')->pay();

        $invoice = Lyra::getInvoice();
        self::assertEquals(10000, $invoice->amount);
        self::assertStringEqualsStringIgnoringLineEndings(Payir::class, $invoice->gateway);

        $url = Lyra::getRedirectUrl();
        self::assertStringContainsString($invoice->token, $url);
    }

    /**
     * @test
     *
     * @return void
     */
    public function verifyOnSuccess(): void
    {
        Lyra::pay(10000, 'sandbox');
        request()->merge([
            'status' => 1,
            'token'  => Lyra::getInvoice()->token,
        ]);
        Lyra::swap(new LyraService());
        self::assertTrue(Lyra::verify(10000, 'sandbox'));
    }

    /**
     * @test
     *
     * @return void
     */
    public function verifyOnFailed(): void
    {
        Lyra::pay(10000, 'sandbox');
        $invoice = Lyra::getInvoice();
        request()->merge([
            'status' => 0, // For instance, user canceled the payment
            'token'  => $invoice->token,
        ]);
        self::expectException(LyraException::class);
        $this->expectExceptionMessage("Verifying Invoice #{$invoice->number} failed!");

        Lyra::verify();
    }

    /**
     * @test
     *
     * @return void
     */
    public function duplicateVerification(): void
    {
        Lyra::pay(10000, 'sandbox');
        $invoice = Lyra::getInvoice();
        request()->merge([
            'status' => 1, // For instance, user canceled the payment
            'token'  => $invoice->token,
        ]);

        self::assertTrue(Lyra::verify());

        self::expectException(\Exception::class);
        $this->expectExceptionMessage('تراکنش تکراریست یا قبلا انجام شده.');

        Lyra::verify();
    }

    /**
     * @test
     *
     * @return void
     */
    public function verifyViaDifferentGateway(): void
    {
        Lyra::pay(10000, 'sandbox');
        request()->merge([
            'status' => 1,
            'token'  => Lyra::getInvoice()->token,
        ]);
        Lyra::swap(new LyraService());
        Lyra::setGateway(IDPay::class, 10000, 'sandbox');

        $this->expectException(LyraException::class);
        $this->expectExceptionMessage('Wrong gateway ['.IDPay::class.'] selected for verification!');

        Lyra::verify();
    }
}

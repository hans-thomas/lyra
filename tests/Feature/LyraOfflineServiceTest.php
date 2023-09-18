<?php

namespace Hans\Lyra\Tests\Feature;

use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Facades\Lyra;
use Hans\Lyra\Helpers\Enums\Status;
use Hans\Lyra\LyraOfflineService;
use Hans\Lyra\Tests\TestCase;
use Illuminate\Http\UploadedFile;

class LyraOfflineServiceTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function pay(): void
    {
        $file = UploadedFile::fake()
                            ->createWithContent(
                                'fake-receipt.jpg',
                                file_get_contents(__DIR__.'/../resources/receipt.jpg')
                            );

        $invoice = Lyra::offline()->pay($file, $amount = 10000)->getInvoice();

        self::assertEquals(Status::PENDING, $invoice->status);
        self::assertEquals('manual', $invoice->gateway);
        self::assertEquals($amount, $invoice->amount);
        self::assertTrue($invoice->offline);
    }

    /**
     * @test
     *
     * @return void
     */
    public function accept(): void
    {
        $file = UploadedFile::fake()
                            ->createWithContent(
                                'fake-receipt.jpg',
                                file_get_contents(__DIR__.'/../resources/receipt.jpg')
                            );

        $invoice = Lyra::offline()->pay($file, 10000)->getInvoice();

        Lyra::swapOffline(new LyraOfflineService());

        self::assertTrue(Lyra::offline()->accept($invoice));
        self::assertEquals(Status::SUCCESS, $invoice->status);

        $invoice = Lyra::offline()->pay($file, 10000)->getInvoice();

        Lyra::swapOffline(new LyraOfflineService());

        self::assertTrue(Lyra::offline()->accept($invoice->id));
        $invoice->refresh();
        self::assertEquals(Status::SUCCESS, $invoice->status);
    }

    /**
     * @test
     *
     * @return void
     */
    public function acceptAnAcceptedInvoice(): void
    {
        $file = UploadedFile::fake()
                            ->createWithContent(
                                'fake-receipt.jpg',
                                file_get_contents(__DIR__.'/../resources/receipt.jpg')
                            );

        $invoice = Lyra::offline()->pay($file, 10000)->getInvoice();

        Lyra::offline()->accept($invoice);

        $this->expectException(LyraException::class);
        $this->expectExceptionMessage("Receipt #$invoice->number is not valid!");

        Lyra::offline()->accept($invoice);
    }

    /**
     * @test
     *
     * @return void
     */
    public function deny(): void
    {
        $file = UploadedFile::fake()
                            ->createWithContent(
                                'fake-receipt.jpg',
                                file_get_contents(__DIR__.'/../resources/receipt.jpg')
                            );

        $invoice = Lyra::offline()->pay($file, 10000)->getInvoice();

        Lyra::swapOffline(new LyraOfflineService());

        self::assertTrue(Lyra::offline()->deny($invoice));
        self::assertEquals(Status::FAILED, $invoice->status);

        $invoice = Lyra::offline()->pay($file, 10000)->getInvoice();

        Lyra::swapOffline(new LyraOfflineService());

        self::assertTrue(Lyra::offline()->deny($invoice->id));
        $invoice->refresh();
        self::assertEquals(Status::FAILED, $invoice->status);
    }

    /**
     * @test
     *
     * @return void
     */
    public function denyAnAcceptedInvoice(): void
    {
        $file = UploadedFile::fake()
                            ->createWithContent(
                                'fake-receipt.jpg',
                                file_get_contents(__DIR__.'/../resources/receipt.jpg')
                            );

        $invoice = Lyra::offline()->pay($file, 10000)->getInvoice();

        Lyra::offline()->accept($invoice);

        $this->expectException(LyraException::class);
        $this->expectExceptionMessage("Receipt #$invoice->number is not valid!");

        Lyra::offline()->deny($invoice);
    }
}

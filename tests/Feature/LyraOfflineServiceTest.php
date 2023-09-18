<?php

namespace Hans\Lyra\Tests\Feature;

use Hans\Lyra\Facades\Lyra;
use Hans\Lyra\Helpers\Enums\Status;
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
                            ->createWithContent('fake-receipt.jpg',
                                file_get_contents(__DIR__.'/../resources/receipt.jpg'));

        $invoice = Lyra::offline()->pay($file, $amount = 10000)->getInvoice();

        self::assertEquals(Status::PENDING, $invoice->status);
        self::assertEquals('manual', $invoice->gateway);
        self::assertEquals($amount, $invoice->amount);
        self::assertTrue($invoice->offline);
    }
}
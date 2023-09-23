<?php

namespace Hans\Lyra\Tests\Unit;

use Hans\Lyra\Gateways\Payir;
use Hans\Lyra\Helpers\Enums\Status;
use Hans\Lyra\Models\Invoice;
use Hans\Lyra\Tests\TestCase;
use Illuminate\Support\Str;

class InvoiceModelTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function createWithNoParam(): void
    {
        $model = $this->makeInvoice();

        self::assertInstanceOf(Invoice::class, $model);
        self::assertIsInt($model->number);
        self::assertLessThan(65535, $model->number);
        self::assertIsString($model->gateway);
        self::assertIsInt($model->amount);
        self::assertInstanceOf(Status::class, $model->status);
        self::assertEquals(Status::PENDING, $model->status);

        self::assertNull($model->token);
        self::assertNull($model->transaction_id);
    }

    /**
     * @test
     *
     * @return void
     */
    public function createWithParams(): void
    {
        $model = $this->makeInvoice([
            'token'          => $token = Str::random(),
            'transaction_id' => $transId = Str::random(),
            'amount'         => $amount = 5.99,
        ]);

        self::assertIsString($model->token);
        self::assertEquals($token, $model->token);

        self::assertIsString($model->transaction_id);
        self::assertEquals($transId, $model->transaction_id);

        self::assertIsFloat($model->amount);
        self::assertEquals($amount, $model->amount);
    }

    protected function makeInvoice(array $data = []): Invoice
    {
        return Invoice::query()
                      ->create(array_merge(['gateway' => Payir::class, 'amount' => rand(10, 5000)], $data))
                      ->refresh();
    }
}

<?php

namespace Hans\Lyra;

use Hans\Alicia\Facades\Alicia;
use Hans\Lyra\Exceptions\LyraErrorCode;
use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Helpers\Enums\Status;
use Hans\Lyra\Models\Invoice;
use Illuminate\Http\UploadedFile;

class LyraOfflineService
{
    private Invoice $invoice;

    public function __construct()
    {
        $this->invoice = $this->findOrCreateInvoice();
    }

    public function pay(UploadedFile $file, int $amount): self
    {
        $this->invoice->amount = $amount;
        $this->invoice->save();

        $file = Alicia::upload($file)->getData();
        $this->invoice->attachTo($file);

        return $this;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice->refresh();
    }

    protected function findOrCreateInvoice(string $number = null): Invoice
    {
        if (is_null($number)) {
            $instance = new Invoice();
            $instance->setOffline();
            $instance->gateway = 'manual';

            return $instance;
        }

        return Invoice::offline()->where('number', $number)->firstOrFail();
    }
}
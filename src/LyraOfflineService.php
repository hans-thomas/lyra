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

    public function pay(UploadedFile $file, int $amount): self
    {
        $this->invoice = $this->findOrCreateInvoice();
        $this->invoice->amount = $amount;
        $this->invoice->save();

        $file = Alicia::upload($file)->getData();
        $this->invoice->attachTo($file);

        return $this;
    }

    public function accept(Invoice|int $invoice): bool
    {
        $this->invoice = is_int($invoice) ? $this->findOrCreateInvoice($invoice) : $invoice;

        throw_unless(
            $this->invoice->isPending(),
            LyraException::make(
                "Receipt #{$this->invoice->number} is not valid!",
                LyraErrorCode::INVOICE_IS_NOT_VALID
            )
        );

        $this->invoice->status = Status::SUCCESS;
        $this->invoice->save();

        return true;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice->refresh();
    }

    protected function findOrCreateInvoice(int $id = null): Invoice
    {
        if (is_null($id)) {
            $instance = new Invoice();
            $instance->setOffline();
            $instance->gateway = 'manual';

            return $instance;
        }

        return Invoice::offline()->findOr(
            $id, callback: fn () => throw LyraException::make("Invoice is not found or not a offline invoice!",
            LyraErrorCode::INVOICE_NOT_FOUND_OR_NOT_OFFLINE)
        );
    }
}
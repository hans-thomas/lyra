<?php

namespace Hans\Lyra;

use Hans\Lyra\Contracts\Gateway;
use Hans\Lyra\Exceptions\LyraErrorCode;
use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Helpers\Enums\Status;
use Hans\Lyra\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class LyraService
{
    private Gateway $gateway;
    private Invoice $invoice;
    private string $gatewayRedirectUrl;

    public function pay(): self
    {
        if (!isset($this->gateway)) {
            throw_if(
                func_num_args() == 0,
                LyraException::make(
                    'Amount of payment is not passed!',
                    LyraErrorCode::AMOUNT_NOT_PASSED
                )
            );
            $this->setGateway(lyra_config('gateways.default'), ...func_get_args());
        }

        $invoiceNumber = generate_unique_invoice_number();
        $token = $this->gateway->request($invoiceNumber);

        $this->invoice = $this->findOrCreateInvoice();
        $this->invoice->token = $token;
        $this->invoice->gateway = get_class($this->gateway);
        $this->invoice->amount = $this->gateway->getAmount();
        $this->invoice->number = $invoiceNumber;

        DB::beginTransaction();
        try {
            $this->invoice->save();
            $this->gatewayRedirectUrl = $this->gateway->pay($token);
        } catch (Throwable $e) {
            DB::rollBack();
            throw LyraException::make(
                'Failed to pay the invoice! '.$e->getMessage(),
                LyraErrorCode::FAILED_TO_PAY
            );
        }
        DB::commit();

        return $this;
    }

    public function getRedirectUrl(): string
    {
        return $this->gatewayRedirectUrl;
    }

    public function redirect(): RedirectResponse
    {
        return redirect()->away($this->gatewayRedirectUrl);
    }

    public function setGateway(string $gateway, int $amount = null, string $mode = null): self
    {
        throw_unless(
            class_exists($gateway),
            LyraException::make(
                "Gateway class [$gateway] is not exists!",
                LyraErrorCode::GATEWAY_CLASS_NOT_FOUNT
            )
        );
        $this->gateway = new $gateway($amount, $mode);

        return $this;
    }

    public function verify(): bool
    {
        if (!isset($this->gateway)) {
            throw_if(
                func_num_args() == 0,
                LyraException::make(
                    'Amount of payment is not passed!',
                    LyraErrorCode::AMOUNT_NOT_PASSED
                )
            );
            $this->setGateway(lyra_config('gateways.default'), ...func_get_args());
        }
        $token = $this->gateway->getTokenFromRequest();
        if (is_null($token)) {
            $gatewayClass = get_class($this->gateway);

            throw LyraException::make(
                "Wrong gateway [$gatewayClass] selected for verification!",
                LyraErrorCode::WRONG_GATEWAY_CLASS_SELECTED
            );
        }

        $this->invoice = $this->findOrCreateInvoice($token);
        $this->gateway->setAmount($this->invoice->amount);

        if (!$this->gateway->verify($this->invoice)) {
            $this->invoice->status = Status::FAILED;
            $this->invoice->save();

            throw LyraException::make(
                "Verifying Invoice #{$this->invoice->number} failed!",
                LyraErrorCode::FAILED_TO_VERIFYING
            );
        }

        $this->invoice->status = Status::SUCCESS;
        $this->invoice->save();

        return true;
    }

    protected function findOrCreateInvoice(string $token = null): Invoice
    {
        if (is_null($token)) {
            return new Invoice();
        }

        return Invoice::query()->where('token', $token)->firstOrFail();
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice->refresh();
    }
}

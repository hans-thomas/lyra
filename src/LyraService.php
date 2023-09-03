<?php

namespace Hans\Lyra;

use Hans\Lyra\Contracts\Gateway;
use Hans\Lyra\Exceptions\LyraErrorCode;
use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Helpers\Enums\Status;
use Hans\Lyra\Models\Invoice;
use Illuminate\Http\RedirectResponse;

class LyraService
{
    private Gateway $gateway;
    private Invoice $invoice;
    private string $gatewayRedirectUrl;

    public function __construct()
    {
        $this->invoice = $this->findOrCreateInvoice();
    }

    public function pay(int $amount): self
    {
        if (!isset($this->gateway)) {
            $this->gateway = $this->setGateway(lyra_config('gateways.default'), $amount);
        }

        $token = $this->gateway->request();
        $this->invoice->token = $token;
        $this->invoice->gateway = $this->gateway::class;
        $this->invoice->amount = $this->gateway::class;
        $this->gatewayRedirectUrl = $this->gateway->pay($token);

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
        $gateway = new $gateway($amount, $mode);

        $this->gateway = $gateway;

        return $this;
    }

    public function verify(): bool
    {
        if (!isset($this->gateway)) {
            $this->gateway = $this->setGateway(lyra_config('gateways.default'));
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
            throw LyraException::make(
                "Verifying Invoice #{$this->invoice->number} failed!",
                LyraErrorCode::FAILED_TO_VERIFYING
            );
        }

        $this->invoice->status = Status::SUCCESS;

        return true;
    }

    protected function findOrCreateInvoice(string $token = null): Invoice
    {
        if (is_null($token)) {
            return new Invoice();
        }

        return Invoice::query()->where('token', $token)->firstOrFail();
    }

    public function __destruct()
    {
        $this->invoice->save();
    }
}

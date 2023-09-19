<?php

namespace Hans\Lyra\Contracts;

use GuzzleHttp\Client;
use Hans\Lyra\Models\Invoice;

abstract class Gateway
{
    /**
     * Settings related to the selected gateway.
     *
     * @var array|mixed
     */
    protected readonly array $settings;

    /**
     * GuzzleHttp instance.
     *
     * @var Client
     */
    protected readonly Client $client;

    /**
     * Gateway mode.
     *
     * @var string|mixed
     */
    protected readonly string $mode;

    /**
     * @param int|null    $amount
     * @param string|null $mode
     */
    public function __construct(
        protected ?int $amount = null,
        string $mode = null
    ) {
        $this->settings = lyra_config('gateways.'.static::class);
        $this->client = new Client(['http_errors' => false]);
        if ($mode and key_exists($mode, $this->settings['modes'])) {
            $this->mode = $mode;
        } else {
            $this->mode = $this->settings['mode'] ?? 'normal';
        }
    }

    /**
     * Send a request to the gateway and receive a token.
     *
     * @param int|string|null $order_id
     *
     * @return string
     */
    abstract public function request(int|string $order_id = null): string;

    /**
     * Build the payment page url.
     *
     * @param string $token
     *
     * @return string
     */
    abstract public function pay(string $token): string;

    /**
     * Verify the purchase on callback.
     *
     * @param Invoice $invoice
     *
     * @return bool
     */
    abstract public function verify(Invoice $invoice): bool;

    /**
     * Extract the unique token from the request.
     *
     * @return string|null
     */
    abstract public function getTokenFromRequest(): ?string;

    /**
     * Return available error list of the gateway.
     *
     * @return array
     */
    abstract public function errorsList(): array;

    /**
     * Return api end-points of the gateway despite the determined mode.
     *
     * @return array
     */
    protected function apis(): array
    {
        return $this->settings['modes'][$this->mode] ?? [];
    }

    /**
     * Determine the sandbox mode is enabled or not.
     *
     * @return bool
     */
    public function isSandboxEnabled(): bool
    {
        return $this->mode == 'sandbox';
    }

    /**
     * Translate the error code to the related message.
     *
     * @param int    $code
     * @param string $default
     *
     * @return string
     */
    protected function translateError(int $code, string $default = 'Failed to process the request!'): string
    {
        if (key_exists($code, $this->errorsList())) {
            return $this->errorsList()[$code];
        }

        return $default;
    }

    /**
     * Set amount of the payment.
     *
     * @param int $amount
     *
     * @return $this
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount of the payment.
     *
     * @return int|null
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }
}

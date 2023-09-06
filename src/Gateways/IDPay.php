<?php

namespace Hans\Lyra\Gateways;

use Hans\Lyra\Contracts\Gateway;
use Hans\Lyra\Models\Invoice;

class IDPay extends Gateway
{
    public function request(int|string $order_id = null): string
    {
        $data = array_diff_key(
            $this->settings,
            array_flip(['mode', 'modes'])
        );
        $data['amount'] = $this->amount;
        $data['order_id'] = $order_id;
        $data = array_filter($data, fn ($item) => !empty($item));
        if ($this->isSandboxEnabled()) {
            $data['X-API-KEY'] = '6a7f99eb-7c20-4412-a972-6dfb7cd253a4';
        }

        $response = $this->client->post(
            $this->apis()['purchase'],
            [
                'json'    => $data,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-KEY'    => $data['X-API-KEY'],
                    'X-SANDBOX'    => $this->isSandboxEnabled() ? 'true' : 'false',
                ],
            ]
        )
                                 ->getBody()
                                 ->getContents();
        $result = json_decode($response, true);

        if (!isset($result['id'])) {
            throw new \Exception($this->translateError($result['error_code']));
        }

        return $result['id'];
    }

    public function pay(string $token): string
    {
        // TODO: Implement pay() method.
    }

    public function verify(Invoice $invoice): bool
    {
        // TODO: Implement verify() method.
    }

    public function getTokenFromRequest(): ?string
    {
        // TODO: Implement getTokenFromRequest() method.
    }

    public function errorsList(): array
    {
        // TODO: Implement errorsList() method.
    }
}
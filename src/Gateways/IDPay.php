<?php

namespace Hans\Lyra\Gateways;

use GuzzleHttp\Exception\GuzzleException;
use Hans\Lyra\Contracts\Gateway;
use Hans\Lyra\Exceptions\LyraErrorCode;
use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Models\Invoice;

class IDPay extends Gateway
{
    /**
     * Send a request to the gateway and receive a token.
     *
     * @param int|string|null $order_id
     *
     * @throws GuzzleException
     *
     * @return string
     */
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

    /**
     * Build the payment page url.
     *
     * @param string $token
     *
     * @return string
     */
    public function pay(string $token): string
    {
        return str_replace(
            ':id',
            $token,
            $this->apis()['payment']
        );
    }

    /**
     * Verify the purchase on callback.
     *
     * @param Invoice $invoice
     *
     * @throws GuzzleException
     * @throws LyraException
     *
     * @return bool
     */
    public function verify(Invoice $invoice): bool
    {
        $callback = request()->only(
            'status',
            'track_id',
            'id',
            'order_id',
            'amount',
            'card_no',
            'hashed_card_no',
            'date'
        );

        if ($callback['status'] != 100) {
            return false;
        }

        if ($invoice->token !== $callback['id']) {
            throw LyraException::make(
                'Token mismatched!',
                LyraErrorCode::TOKEN_MISMATCHED
            );
        }

        if ($invoice->number !== $callback['order_id']) {
            throw LyraException::make(
                'order_id mismatched!',
                LyraErrorCode::ORDER_ID_MISMATCHED
            );
        }

        $data = [
            'id'       => $this->settings['X-API-KEY'],
            'order_id' => $invoice->number,
        ];

        if ($this->isSandboxEnabled()) {
            $result = [
                'status'   => '100',
                'track_id' => '10012',
                'id'       => 'd2e353189823079e1e4181772cff5292',
                'order_id' => '101',
                'amount'   => '10000',
                'date'     => '1546288200',
                'payment'  => [
                    'track_id'       => '888001',
                    'amount'         => '10000',
                    'card_no'        => '123456******1234',
                    'hashed_card_no' => 'E59FA6241C94B8836E3D03120DF33E80FD988888BBA0A122240C2E7D23B48295',
                    'date'           => '1546288500',
                ],
                'verify'   => [
                    'date' => '1546288800',
                ],
            ];
        } else {
            $response = $this->client->post(
                $this->apis()['verification'],
                [
                    'json'    => $data,
                    'headers' => [
                        'Accept'    => 'application/json',
                        'X-API-KEY' => $data['X-API-KEY'],
                        'X-SANDBOX' => $this->isSandboxEnabled() ? 'true' : 'false',
                    ],
                ]
            )
                                     ->getBody()
                                     ->getContents();
            $result = json_decode($response, true);
        }

        if (Invoice::query()->where('transaction_id', $result['track_id'])->exists() or $result['status'] == 101) {
            throw new \Exception($this->translateError(53));
        }

        if ($result['status'] != 100) {
            return false;
        }

        $invoice->transaction_id = $result['track_id'];
        $invoice->save();

        return true;
    }

    /**
     * Extract the unique token from the request.
     *
     * @return string|null
     */
    public function getTokenFromRequest(): ?string
    {
        return request('id');
    }

    /**
     * Return available error list of the gateway.
     *
     * @return array
     */
    public function errorsList(): array
    {
        return [
            '-1' => 'خطای غیر منتظره',
            '11' => 'کاربر مسدود شده است.',
            '12' => 'API Key یافت نشد.',
            '13' => 'درخواست شما از {ip} ارسال شده است. این IP با IP های ثبت شده در وب سرویس همخوانی ندارد.',
            '14' => 'وب سرویس شما در حال بررسی است و یا تایید نشده است.',
            '15' => 'سرویس مورد نظر در دسترس نمی باشد.',
            '21' => 'حساب بانکی متصل به وب سرویس تایید نشده است.',
            '22' => 'وب سریس یافت نشد.',
            '23' => 'اعتبار سنجی وب سرویس ناموفق بود.',
            '24' => 'حساب بانکی مرتبط با این وب سرویس غیر فعال شده است.',
            '31' => 'کد تراکنش id نباید خالی باشد.',
            '32' => 'شماره سفارش order_id نباید خالی باشد.',
            '33' => 'مبلغ amount نباید خالی باشد.',
            '34' => 'مبلغ amount باید بیشتر از {min-amount} ریال باشد.',
            '35' => 'مبلغ amount باید کمتر از {max-amount} ریال باشد.',
            '36' => 'مبلغ amount بیشتر از حد مجاز است.',
            '37' => 'آدرس بازگشت callback نباید خالی باشد.',
            '38' => 'درخواست شما از آدرس {domain} ارسال شده است. دامنه آدرس بازگشت callback با آدرس ثبت شده در وب سرویس همخوانی ندارد.',
            '39' => 'آدرس بازگشت callback نامعتبر است.',
            '41' => 'فیلتر وضعیت تراکنش ها می بایست آرایه ای (لیستی) از وضعیت های مجاز در مستندات باشد.',
            '42' => 'فیلتر تاریخ پرداخت می بایست آرایه ای شامل المنت های min و max از نوع timestamp باشد.',
            '43' => 'فیلتر تاریخ تسویه می بایست آرایه ای شامل المنت های min و max از نوع timestamp باشد.',
            '44' => 'فیلتر تراکنش صحیح نمی باشد.',
            '51' => 'تراکنش ایجاد نشد.',
            '52' => 'استعلام نتیجه ای نداشت.',
            '53' => 'تایید پرداخت امکان پذیر نیست.',
            '54' => 'مدت زمان تایید پرداخت سپری شده است.',
        ];
    }
}

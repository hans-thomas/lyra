<?php

namespace Hans\Lyra\Gateways;

use GuzzleHttp\Exception\GuzzleException;
use Hans\Lyra\Contracts\Gateway;
use Hans\Lyra\Exceptions\LyraErrorCode;
use Hans\Lyra\Exceptions\LyraException;
use Hans\Lyra\Models\Invoice;

class Payir extends Gateway
{
    /**
     * Send a request to the gateway and receive a token
     *
     * @param  int|string|null  $order_id
     *
     * @return string
     * @throws GuzzleException
     */
    public function request(int|string $order_id = null): string
    {
        $data = array_diff_key(
            $this->settings,
            array_flip(['mode', 'modes'])
        );
        $data['amount'] = $this->amount;
        $data = array_filter($data, fn ($item) => !empty($item));

        if ($this->isSandboxEnabled()) {
            $data['api'] = 'test';
        }

        $response = $this->client->post(
            $this->apis()['purchase'],
            [
                'json'    => $data,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        )
                                 ->getBody()
                                 ->getContents();
        $result = json_decode($response, true);

        if ($result['status'] == 1) {
            return $result['token'];
        }

        throw new \Exception($this->translateError($result['errorCode']));
    }

    /**
     * Build the payment page url
     *
     * @param  string  $token
     *
     * @return string
     */
    public function pay(string $token): string
    {
        return str_replace(
            ':token',
            $token,
            $this->apis()['payment']
        );
    }

    /**
     * Verify the purchase on callback
     *
     * @param  Invoice  $invoice
     *
     * @return bool
     * @throws LyraException
     * @throws GuzzleException
     */
    public function verify(Invoice $invoice): bool
    {
        $status = request('status');
        $token = $this->getTokenFromRequest();

        // User canceled the purchase
        if ($status != 1) {
            return false;
        }

        if ($invoice->token !== $token) {
            throw LyraException::make(
                'Token mismatched!',
                LyraErrorCode::TOKEN_MISMATCHED
            );
        }

        $data = [
            'api'   => $this->settings['api'],
            'token' => $token,
        ];

        if ($this->isSandboxEnabled()) {
            $result = [
                'status'       => 1,
                'transId'      => 'fake-static-transId',
            ];
        } else {
            $response = $this->client->post(
                $this->apis()['verification'],
                [
                    'json'    => $data,
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]
            )
                                     ->getBody()
                                     ->getContents();
            $result = json_decode($response, true);
        }

        if ($result['status'] != 1) {
            return false;
        }
        // TODO: fire event

        if (Invoice::query()->where('transaction_id', $result['transId'])->exists()) {
            throw new \Exception($this->translateError(-6));
        }

        $invoice->transaction_id = $result['transId'];
        $invoice->save();

        return true;
    }

    /**
     * Extract the unique token from the request
     *
     * @return string|null
     */
    public function getTokenFromRequest(): ?string
    {
        return request('token');
    }

    /**
     * Return available error list of the gateway
     *
     * @return array
     */
    public function errorsList(): array
    {
        return [
            '0'   => 'درحال حاضر درگاه بانکی قطع شده و مشکل بزودی برطرف می شود.',
            '-1'  => 'API Key .ارسال نمی شود',
            '-2'  => 'Token .ارسال نمی شود',
            '-3'  => 'API Key .ارسال شده اشتباه است',
            '-4'  => 'امکان انجام تراکنش برای این پذیرنده وجود ندارد.',
            '-5'  => 'تراکنش با خطا مواجه شده است.',
            '-6'  => 'تراکنش تکراریست یا قبلا انجام شده.',
            '-7'  => 'مقدار Token ارسالی اشتباه است.',
            '-8'  => 'شماره تراکنش ارسالی اشتباه است.',
            '-9'  => 'زمان مجاز برای انجام تراکنش تمام شده.',
            '-10' => 'مبلغ تراکنش ارسال نمی شود.',
            '-11' => 'مبلغ تراکنش باید به صورت عددی و با کاراکترهای لاتین باشد.',
            '-12' => 'مبلغ تراکنش می بایست عددی بین 10,000 و 500,000,000 ریال باشد.',
            '-13' => 'مقدار آدرس بازگشتی ارسال نمی شود.',
            '-14' => 'آدرس بازگشتی ارسالی با آدرس درگاه ثبت شده در شبکه پرداخت پی یکسان نیست.',
            '-15' => 'امکان وریفای وجود ندارد. این تراکنش پرداخت نشده است.',
            '-16' => 'یک یا چند شماره موبایل از اطلاعات پذیرندگان ارسال شده اشتباه است.',
            '-17' => 'میزان سهم ارسالی باید بصورت عددی و بین 1 تا 100 باشد.',
            '-18' => 'فرمت پذیرندگان صحیح نمی باشد.',
            '-19' => 'هر پذیرنده فقط یک سهم میتواند داشته باشد.',
            '-20' => 'مجموع سهم پذیرنده ها باید 100 درصد باشد.',
            '-21' => 'Reseller ID .ارسالی اشتباه است',
            '-22' => 'فرمت یا طول مقادیر ارسالی به درگاه اشتباه است.',
            '-23' => 'سوییچ PSP ( درگاه بانک ) قادر به پردازش درخواست نیست. لطفا لحظاتی بعد مجددا تلاش کنید.',
            '-24' => 'شماره کارت باید بصورت 16 رقمی، لاتین و چسبیده بهم باشد.',
            '-25' => 'امکان استفاده از سرویس در کشور مبدا شما وجود نداره.',
            '-26' => 'امکان انجام تراکنش برای این درگاه وجود ندارد.',
            '-27' => 'در انتظار تایید درگاه توسط شاپرک.',
            '-28' => 'امکان تسهیم تراکنش برای این درگاه وجود ندارد.',
        ];
    }
}

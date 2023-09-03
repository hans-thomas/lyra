<?php

namespace Hans\Lyra\Gateways;

    use Hans\Lyra\Contracts\Gateway;

    class Payir extends Gateway
    {
        public function request(): string
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

        public function pay(string $token): string
        {
            return str_replace(
                ':token',
                $token,
                $this->apis()['payment']
            );
        }

        public function verify(): bool
        {
            $status = request('status');
            $token = request('token');

            if ($status != 1) {
                // User canceled the purchase
                return false;
            }

            if ($this->isSandboxEnabled()) {
                return true;
            }

            // TODO: Compare $token with stored token on pay stage

            $data = [
                'api'   => $this->settings['api'],
                'token' => $token,
            ];

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

            if ($result['status'] !== 1) {
                return false;
            }

			// TransactionIdReceived

            // TODO: If transId exists on DB, throw an error: -6
            // TODO: Store transId on DB

            return true;
        }

        public function errorsList(): array
        {
            return [
                '0'   => 'درحال حاضر درگاه بانکی قطع شده و مشکل بزودی برطرف می شود',
                '-1'  => 'API Key ارسال نمی شود',
                '-2'  => 'Token ارسال نمی شود',
                '-3'  => 'API Key ارسال شده اشتباه است',
                '-4'  => 'امکان انجام تراکنش برای این پذیرنده وجود ندارد',
                '-5'  => 'تراکنش با خطا مواجه شده است',
                '-6'  => 'تراکنش تکراریست یا قبلا انجام شده',
                '-7'  => 'مقدار Token ارسالی اشتباه است',
                '-8'  => 'شماره تراکنش ارسالی اشتباه است',
                '-9'  => 'زمان مجاز برای انجام تراکنش تمام شده',
                '-10' => 'مبلغ تراکنش ارسال نمی شود',
                '-11' => 'مبلغ تراکنش باید به صورت عددی و با کاراکترهای لاتین باشد',
                '-12' => 'مبلغ تراکنش می بایست عددی بین 10,000 و 500,000,000 ریال باشد',
                '-13' => 'مقدار آدرس بازگشتی ارسال نمی شود',
                '-14' => 'آدرس بازگشتی ارسالی با آدرس درگاه ثبت شده در شبکه پرداخت پی یکسان نیست',
                '-15' => 'امکان وریفای وجود ندارد. این تراکنش پرداخت نشده است',
                '-16' => 'یک یا چند شماره موبایل از اطلاعات پذیرندگان ارسال شده اشتباه است',
                '-17' => 'میزان سهم ارسالی باید بصورت عددی و بین 1 تا 100 باشد',
                '-18' => 'فرمت پذیرندگان صحیح نمی باشد',
                '-19' => 'هر پذیرنده فقط یک سهم میتواند داشته باشد',
                '-20' => 'مجموع سهم پذیرنده ها باید 100 درصد باشد',
                '-21' => 'Reseller ID ارسالی اشتباه است',
                '-22' => 'فرمت یا طول مقادیر ارسالی به درگاه اشتباه است',
                '-23' => 'سوییچ PSP ( درگاه بانک ) قادر به پردازش درخواست نیست. لطفا لحظاتی بعد مجددا تلاش کنید',
                '-24' => 'شماره کارت باید بصورت 16 رقمی، لاتین و چسبیده بهم باشد',
                '-25' => 'امکان استفاده از سرویس در کشور مبدا شما وجود نداره',
                '-26' => 'امکان انجام تراکنش برای این درگاه وجود ندارد',
                '-27' => 'در انتظار تایید درگاه توسط شاپرک',
                '-28' => 'امکان تسهیم تراکنش برای این درگاه وجود ندارد',
            ];
        }
    }

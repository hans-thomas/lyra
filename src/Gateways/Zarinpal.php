<?php

namespace Hans\Lyra\Gateways;

    use Exception;
    use Hans\Lyra\Contracts\Gateway;

    class Zarinpal extends Gateway
    {
        public function request(): string
        {
            $data = array_diff_key(
                $this->settings,
                array_flip(['mode', 'modes'])
            );
            $data['amount'] = $this->amount;
            if ($this->isSandboxEnabled()) {
                $data['merchant_id'] = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
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
            if (empty($result['errors']) and $result['data']['code'] == 100) {
                return $result['data']['authority'];
            }

            throw new Exception($this->translateError($result['errors']['code']));
        }

        public function pay(): string
        {
            // TODO: Store authority on DB
            $authority = $this->request();

            return str_replace(
                ':authority',
                $authority,
                $this->apis()['payment']
            );
        }

        public function verify(): bool
        {
            $status = request('Status');
            $authority = request('Authority');

            if ($status !== 'OK') {
                // User canceled the purchase
                return false;
            }

            if ($this->isSandboxEnabled()) {
                return true;
            }

            // TODO: Compare authority with stored authority on DB

            $data = [
                'merchant_id' => $this->settings['merchant_id'],
                'amount'      => 1000, // TODO: fetch from DB
                'authority'   => $authority,
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

            if (!empty($result['errors'])) {
                return false;
            }

            if ($result['data']['code'] == 101) {
                throw new Exception($this->translateError(101));
            }

            // TODO: Store ref_id on DB

            return true;
        }

        public function errorsList(): array
        {
            return [
                '-9'  => 'خطای اعتبار سنجی',
                '-10' => 'ای پی و يا مرچنت كد پذيرنده صحيح نمی باشد.',
                '-11' => 'مرچنت کد فعال نیست لطفا با تیم پشتیبانی ما تماس بگیرید.',
                '-12' => 'تلاش بیش از حد در یک بازه زمانی کوتاه',
                '-15' => 'درگاه پرداخت به حالت تعلیق در آمده است، پذیرنده مشکل خود را به امور مشتریان زرین‌پال ارجاع دهد.',
                '-16' => 'سطح تاييد پذيرنده پايين تر از سطح نقره ای می باشد.',
                '-17' => 'محدودیت پذیرنده در سطح آبی',
                '100' => 'تراکنش با موفقیت انجام گردید.',
                '-30' => 'اجازه دسترسی به تسویه اشتراکی شناور ندارید.',
                '-31' => 'حساب بانکی تسویه را به پنل اضافه کنید. مقادیر وارد شده برای تسهیم درست نیست. پذیرنده جهت استفاده از خدمات سرویس تسویه اشتراکی شناور، باید حساب بانکی معتبری به پنل کاربری خود اضافه نماید.',
                '-32' => 'مبلغ وارد شده از مبلغ کل تراکنش بیشتر است.',
                '-33' => 'درصد های وارد شده صحيح نمی باشد.',
                '-34' => 'مبلغ از کل تراکنش بیشتر است.',
                '-35' => 'تعداد افراد دریافت کننده تسهیم بیش از حد مجاز است.',
                '-36' => 'حداقل مبلغ جهت تسهیم باید ۱۰۰۰۰ ریال باشد.',
                '-37' => 'یک یا چند شماره شبای وارد شده برای تسهیم از سمت بانک غیر فعال است.',
                '-38' => 'خطا٬عدم تعریف صحیح شبا٬لطفا دقایقی دیگر تلاش کنید.',
                '-39' => 'خطایی رخ داده است به امور مشتریان زرین پال اطلاع دهید.',
                '-40' => 'پارامترهای اضافی نامعتبر، expire_in معتبر نیست.',
                '-50' => 'مبلغ پرداخت شده با مقدار مبلغ در وریفای متفاوت است.',
                '-51' => 'پرداخت ناموفق',
                '-52' => 'خطای غیر منتظره‌ای رخ داده است. پذیرنده مشکل خود را به امور مشتریان زرین‌پال ارجاع دهد.',
                '-53' => 'پرداخت متعلق به این مرچنت کد نیست.',
                '-54' => 'اتوریتی نامعتبر است.',
                '101' => 'عمليات پرداخت موفق بوده و قبلا عملیات وریفای تراكنش انجام شده است.',
            ];
        }
    }

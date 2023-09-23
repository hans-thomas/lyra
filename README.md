# Lyra

<p align="center"><img alt="lyra banner" src="assets/starter-banner.png"></p>

[![codecov](https://codecov.io/gh/hans-thomas/lyra/branch/master/graph/badge.svg?token=X1D6I0JLSZ)](https://codecov.io/gh/hans-thomas/lyra)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/hans-thomas/lyra/php.yml)
![GitHub top language](https://img.shields.io/github/languages/top/hans-thomas/lyra)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/hans-thomas/lyra)
![StyleCi](https://github.styleci.io/repos/681052866/shield?style=plastic)

Lyra is a payment gateway that supports offline purchase as well. There are two defined gateway by default, but you can
implement your own gateway(s).

## Installation

Via composer

```bash
composer require hans-thomas/lyra
```

Then publish the config file

```bash
php artisan vendor:publish --tag lyra-config
```

## Usage

Lyra supports online and offline modes. First we are going to introduce the online mode.

### Online purchase

#### Pay

To pay a purchase, you can simply call `pay` method and pass the amount.

```php
Lyra::pay(10000);
```

#### getRedirectUrl

After calling `pay` method, you can call `getRedirectUrl` method to get the gateway url as string.

```php
Lyra::pay(10000)->getRedirectUrl();
```

#### redirect

Also, you can call `redirect` method to redirect the user to the gateway url after calling the `pay` method.

```php
Lyra::pay(10000)->redirect();
```

#### setGateway

You can set another gateway **before calling the `pay` method** and override the default one.

```php
Lyra::setGateway(Payir::class, 10000)->pay();
```

#### verify

To verify the purchase, on callback, you can call `verify` method and pass the amount of payment.

```php
Lyra::verify(10000);
```

#### getInvoice

After calling `pay` method, you can get the created invoice using `getInvoice` method.

```php
Lyra::pay(10000)->getInvoice();
```

### Offline purchase

#### pay

To purchase an offline payment, you must call `offline` method first and then `pay` method.

```php
Lyra::offline()->pay($file, $amount = 10000);
```

#### getInvoice

Also, in offline mode, you can call `getInvoice` method to get the created invoice after the `pay` method.

```php
Lyra::offline()->pay($file, 10000)->getInvoice();
```

#### accept

To accept an offline purchase, call `accept` method and pass the related invoice.

```php
Lyra::offline()->accept($invoice);
```

#### deny

To deny a purchase, call `deny` method.

```php
Lyra::offline()->deny($invoice);
```

## Contributing

1. Fork it!
2. Create your feature branch: git checkout -b my-new-feature
3. Commit your changes: git commit -am 'Add some feature'
4. Push to the branch: git push origin my-new-feature
5. Submit a pull request ❤️

Support
-------

- [Report bugs](https://github.com/hans-thomas/lyra/issues)


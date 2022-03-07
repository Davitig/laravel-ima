# Laravel Ima
With `laravel-ima` package you can easily implement ECOMM 3D-Secure system to your laravel application

## About

The Integrated Merchant Agent allows you to integrate merchants into the
ECOMM 3D-Secure system for Internet-Sourced Transaction Processing

## Features

- Start the single message system (SMS) transaction
- start the dual message system (DMS) authorization
- Execute the dual message system (DMS) transaction
- Start the single message system (SMS) transaction for recurring payment.
- Start the dual message system (DMS) authorization recurring payment.
- Start recurring payment transaction.
- Execute the authorization on a certain amount and register a recurring payment.
- Execute the recurring payment.
- Get transaction result
- Reverse transaction
- Refund transaction
- Execute the credit transaction.
- Close business day

## Installation

Require the `Davitig/laravel-ima` package in your `composer.json` and update your dependencies:
```sh
composer require Davitig/laravel-ima
```

Add the ServiceProvider to the providers array in config/app.php

    Davitig\Ima\ImaServiceProvider::class,

You can optionally use the facade for shorter code. Add this to your facades:

    'Ima' => Davitig\Ima\Facades\Ima::class,

## Usage

```PHP
$result = Ima::startSMSTrans($amount);

if ($result->success()) {
    $transId = $result->getTransId();

    // Your code before redirecting to the merchant.

    $result->redirectToPayment();
}

if ($result->failed()) {
    // Failed response.
}

if ($result->isError()) {
    // Merchant system error
}

// You can also check the full result data
$result->getResult(); // Collection of the result data
$result->getRawResult(); // Raw result data
```

## Configuration

Add the following keys into the `.env` file to configure the package:

`IMA_MERCHANT_HANDLER`
`IMA_CLIENT_HANDLER`
`IMA_CERT_PATH`
`IMA_KEY_PATH`
`IMA_PASS`
`IMA_CURRENCY`

The default configuration settings are set in `config/ima.php`. To modify the file, you can publish the config using this command:

    php artisan vendor:publish --provider="Davitig\Ima\ImaServiceProvider"

## License

Released under the MIT License, see [LICENSE](LICENSE).

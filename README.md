# Omnipay: GlobalPayments

**GlobalPayments driver for the Omnipay PHP payment library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+.

This package implements only GlobalPayments support for Omnipay 2.x Off-sites,
where the customer is redirected to enter payment details (aka HPP - Hosted Payment Page).

It implements the "ecommerce" version of their integration: https://developer.globalpay.com/ecommerce/payments-start

For refunds, their ecommerce API is used, via the SDK: https://github.com/globalpayments/php-sdk 

For Apple Pay, please pass in a "applePayToken" to the purchase() function: https://developer.globalpay.com/ecommerce/applepay#api

## Installation

This package is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "digitickets/omnipay-global-payments": "^0.*"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## Basic Usage

The following gateways are provided by this package:

* GlobalPayments

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

For the refund() function, please pass in a refundPassword. Also make sure you are setting originalTransactionId, transactionRef, and authCode.

This is a sample code of standard Off-site controller using the driver.

### Request a payment
```
// Gateway setup
$gateway = $this->gatewayFactory('GlobalPayments');

// Pluigns specific parameters
gateway->setMerchantId('00000001');
$gateway->setAccount(123);
$gateway->setSharedSecret('asdqweasdzxc');

// Create or fetch your product transaction
$transaction = $this->createTransaction($request);

// Get the data ready for the payment
// Please note that even off-site gateways make use of the CreditCard object,
// because often you need to pass customer billing or shipping details through to the gateway.
$cardData = $transaction->asOmniPay;
$itemsBag = $this->requestItemsBag($request);

// Authorize request
$request = $gateway->purchase(array(
    'amount' => $transaction->amount,
    'currency' => $transaction->currency,
    'card' => $cardData,
    'returnUrl' => $this->generateCallbackUrl(
        'GlobalPayments',
        $transaction->id
    ),
    'transactionId' => $transaction->id,
    'description' => $transaction->description,
    'items' => $itemsBag,
));

// Send request
$response = $request->send();

// Process response
$this->processResponse($response);
```

### Process payment result
```
// Fetch transaction details
$transaction = Transaction::findOrFail($transactionId);

// Gateway setup
$gateway = $this->gatewayFactory('GlobalPayments');

// Pluigns specific parameters
gateway->setMerchantId('00000001');
$gateway->setAccount(123);
$gateway->setSharedSecret('asdqweasdzxc');

// Get the data ready to complete the payment. Since this is typically a stateless callback
// we need to first retrieve our original product transaction details
$params = [
    "amount" => $transaction->amount,
    "currency" => $transaction->currency,
    'returnUrl' => $this->generateCallbackUrl(
        'GlobalPayments',
        $transaction->id
    ),
    'transactionId' => $transaction->id,
    'transactionReference' => $transaction->ref,
];

// Complete purchase request
$request = $gateway->completePurchase($params);

// Send request
$response = $request->send();

// Process response
$this->processResponse($response);
```

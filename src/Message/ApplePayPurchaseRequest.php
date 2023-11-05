<?php

namespace Omnipay\GlobalPayments\Message;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use Omnipay\Common\Message\ResponseInterface;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\ServicesContainer;

/**
 * Uses the Global Payments API to send an encrypted payment token retrieved from Apple Pay to make a payment.
 * https://developer.globalpay.com/ecommerce/applepay#api
 */
class ApplePayPurchaseRequest extends AbstractPurchaseRequest
{

    public function getData(): array
    {
        return [];
    }

    /**
     * @throws ApiException
     */
    public function sendData($data): ResponseInterface
    {
        $config = new GpEcomConfig();
        $config->merchantId = $this->getMerchantId();
        $config->accountId = $this->getAccount();
        $config->sharedSecret = $this->getSharedSecret();
        $config->environment = Environment::PRODUCTION;
        if ($this->getTestMode()) {
            $config->environment = Environment::TEST;
        }

        ServicesContainer::configureService($config);

        $card = new CreditCardData();
        $card->token = $this->getApplePayToken();
        $card->mobileType = EncyptedMobileType::APPLE_PAY;

        $gatewayCard = $this->getCard();
        $customer = new Customer();
        $address = new Address();
        $address->type = AddressType::BILLING;
        $address->streetAddress1 = $gatewayCard->getBillingAddress1();
        $address->streetAddress2 = $gatewayCard->getBillingAddress2();
        $address->city = $gatewayCard->getBillingCity();
        $address->postalCode = $gatewayCard->getBillingPostcode();
        // Set using magic __set. Handles any country code format.
        $address->countryCode = $gatewayCard->getBillingCountry();

        $customer->firstName = $gatewayCard->getBillingFirstName();
        $customer->lastName = $gatewayCard->getBillingLastName();
        $customer->title = $gatewayCard->getBillingTitle();
        $customer->email = $gatewayCard->getEmail();
        $customer->address = $address;
        // Note omnipay doesn't support a customer ID, test without one
        // $customer->id = "";

        // Perform an auto-settled Apple Pay payment.
        $builder = $card
            ->charge($this->getAmount())
            // Note withModifier() is hinted as "internal", but is still used by all the tests and the docs, with no other way to set!
            // Also note the type hint is wrong (TransactionModifier), it actually accepts a string.
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->withCurrency($this->getCurrency())
            ->withOrderId($this->getTransactionId()) // Our transaction ID
            ->withCustomerData($customer);

        if ($this->getClientIp()) {
            $builder->withCustomerIpAddress($this->getClientIp());
        }


        $transaction = $builder->execute();

        return $this->response = new ApplePayPurchaseResponse(
            $this,
            $transaction
        );
    }

    /**
     * Token retrieved using Apple Pay's SDK.
     * Example: {"version":"EC_v1","data":"dvMNzlcy6WNB","header":{"ephemeralPublicKey":"123","transactionId":"123","publicKeyHash":"123"}}
     * @return string|null
     */
    public function getApplePayToken()
    {
        return $this->getParameter('applePayToken');
    }

    public function setApplePayToken($value)
    {
        return $this->setParameter('applePayToken', $value);
    }

}

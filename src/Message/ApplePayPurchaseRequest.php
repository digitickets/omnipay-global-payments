<?php

namespace Omnipay\GlobalPayments\Message;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\EncyptedMobileType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
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

        $gatewayCard = $this->getCard();

        $card = new CreditCardData();
        $card->token = $this->getApplePayToken();
        $card->mobileType = EncyptedMobileType::APPLE_PAY;
        // Note this doesn't seem to show up properly in their dashboard, but maybe GP will fix it at some point...
        $card->cardHolderName = $gatewayCard->getBillingName();

        // Perform an auto-settled Apple Pay payment.
        // Note sending any customer information (name or address) just breaks on their end (doesn't match their schema for wallet payments).
        $builder = $card
            ->charge($this->getAmount())
            // Note withModifier() is hinted as "internal", but is still used by all the tests and the docs, with no other way to set!
            // Also note the type hint is wrong (TransactionModifier), it actually accepts a string.
            ->withModifier(TransactionModifier::ENCRYPTED_MOBILE)
            ->withCurrency($this->getCurrency())
            ->withOrderId($this->getTransactionId()); // Our transaction ID

        if ($this->getClientIp()) {
            $builder->withCustomerIpAddress($this->getClientIp());
        }

        try {
            $transaction = $builder->execute();
        } catch (GatewayException $e) {
            // The SDK throws this for any kind of non-success payment status returned by the GP API, which is annoying.
            return $this->response = new ApplePayPurchaseFailedResponse(
                $this,
                $e->responseMessage,
                $e->responseCode,
                $this->getTransactionId()
            );
        }

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

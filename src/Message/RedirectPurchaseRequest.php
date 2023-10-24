<?php

namespace Omnipay\GlobalPayments\Message;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use function is_numeric;
use League\ISO3166\Exception\InvalidArgumentException;
use League\ISO3166\ISO3166;
use Omnipay\Common\CreditCard;
use function strlen;
use Omnipay\Common\Message\ResponseInterface;

class RedirectPurchaseRequest extends AbstractPurchaseRequest
{
    const TIMESTAMP = 'TIMESTAMP';
    const MERCHANT_ID = 'MERCHANT_ID';
    const ACCOUNT = 'ACCOUNT';
    const ORDER_ID = 'ORDER_ID';
    const AMOUNT = 'AMOUNT';
    const CURRENCY = 'CURRENCY';
    const MERCHANT_RESPONSE_URL = 'MERCHANT_RESPONSE_URL';
    const AUTO_SETTLE_FLAG = 'AUTO_SETTLE_FLAG';

    const HPP_VERSION = 'HPP_VERSION';
    const HPP_CUSTOMER_EMAIL = 'HPP_CUSTOMER_EMAIL';
    const HPP_CUSTOMER_PHONENUMBER_MOBILE = 'HPP_CUSTOMER_PHONENUMBER_MOBILE';
    const HPP_BILLING_STREET1 = 'HPP_BILLING_STREET1';
    const HPP_BILLING_STREET2 = 'HPP_BILLING_STREET2';
    const HPP_BILLING_STREET3 = 'HPP_BILLING_STREET3';
    const HPP_BILLING_CITY = 'HPP_BILLING_CITY';
    const HPP_BILLING_STATE = 'HPP_BILLING_STATE';
    const HPP_BILLING_POSTALCODE = 'HPP_BILLING_POSTALCODE';
    const HPP_BILLING_COUNTRY = 'HPP_BILLING_COUNTRY';
    const HPP_SHIPPING_STREET1 = 'HPP_SHIPPING_STREET1';
    const HPP_SHIPPING_STREET2 = 'HPP_SHIPPING_STREET2';
    const HPP_SHIPPING_STREET3 = 'HPP_SHIPPING_STREET3';
    const HPP_SHIPPING_CITY = 'HPP_SHIPPING_CITY';
    const HPP_SHIPPING_STATE = 'HPP_SHIPPING_STATE';
    const HPP_SHIPPING_POSTALCODE = 'HPP_SHIPPING_POSTALCODE';
    const HPP_SHIPPING_COUNTRY = 'HPP_SHIPPING_COUNTRY';
    const HPP_ADDRESS_MATCH_INDICATOR = 'HPP_ADDRESS_MATCH_INDICATOR';
    const HPP_CHALLENGE_REQUEST_INDICATOR = 'HPP_CHALLENGE_REQUEST_INDICATOR';

    /**
     * Returns the data on the request.
     *
     * It calculates the HASH that we need to send to the provider as well.
     *
     * @return array
     */
    public function getData() : array
    {
        $this->validate('amount', 'card');
        $card = $this->getCard();

        $data = [
            static::TIMESTAMP => gmdate('YmdHis'),
            static::MERCHANT_ID => $this->getMerchantId(),
            static::ACCOUNT => $this->getAccount(),
            static::ORDER_ID => $this->getTransactionId(),
            static::AMOUNT => $this->getAmountInteger(),
            static::CURRENCY => $this->getCurrency(),
            static::MERCHANT_RESPONSE_URL => $this->getReturnUrl(),
            static::AUTO_SETTLE_FLAG => true,
            static::HPP_VERSION => 2,
            static::HPP_CUSTOMER_EMAIL => $card->getEmail(),
            static::HPP_CUSTOMER_PHONENUMBER_MOBILE => $this->formattedPhoneNumber($card),
            static::HPP_BILLING_STREET1 => $card->getBillingAddress1(),
            static::HPP_BILLING_STREET2 => $card->getBillingAddress2(),
            static::HPP_BILLING_STREET3 => '',
            static::HPP_BILLING_CITY => $card->getBillingCity(),
            static::HPP_BILLING_STATE => $card->getBillingState(),
            static::HPP_BILLING_POSTALCODE => $card->getBillingPostcode(),
            static::HPP_BILLING_COUNTRY => $this->getCountry(
                $card->getBillingCountry(),
                'numeric'
            ),
            static::HPP_SHIPPING_STREET1 => $card->getShippingAddress1(),
            static::HPP_SHIPPING_STREET2 => $card->getShippingAddress2(),
            static::HPP_SHIPPING_STREET3 => '',
            static::HPP_SHIPPING_CITY => $card->getShippingCity(),
            static::HPP_SHIPPING_STATE => $card->getShippingState(),
            static::HPP_SHIPPING_POSTALCODE => $card->getShippingPostcode(),
            static::HPP_SHIPPING_COUNTRY => $this->getCountry(
                $card->getShippingCountry(),
                'numeric'
            ),
            static::HPP_ADDRESS_MATCH_INDICATOR => false,
            static::HPP_CHALLENGE_REQUEST_INDICATOR => 'NO_PREFERENCE',
        ];

        $data['SHA1HASH'] = $this->createSignature($data, 'sha1');

        return $data;
    }

    /**
     * Creates the signature needed to send to the provider
     *
     * @param array  $data
     * @param string $method
     *
     * @return string
     */
    public function createSignature($data, $method = 'sha1') : string
    {
        $hash = $method(rtrim(implode('.', [
            $data[static::TIMESTAMP],
            $data[static::MERCHANT_ID],
            $data[static::ORDER_ID],
            $data[static::AMOUNT],
            $data[static::CURRENCY],
        ]), '.'));

        return $method($hash . '.' . $this->getSharedSecret());
    }

    /**
     * $expectedFormat = name | alpha2 | alpha3 | numeric | currency
     *
     * @param        $country
     * @param string $expectedFormat
     *
     * @return array
     */
    public function getCountry($country, string $expectedFormat = null)
    {
        try {
            if (is_numeric($country)) {
                $data = (new ISO3166)->numeric($country);
            } elseif (strlen($country) == 3) {
                $data = (new ISO3166)->alpha3($country);
            } elseif (strlen($country) == 2) {
                $data = (new ISO3166)->alpha2($country);
            } else {
                $data = (new ISO3166)->name($country);
            }
        } catch (InvalidArgumentException $e) {
            $data = null;
        }

        if ($expectedFormat && $data) {
            $data = $data[$expectedFormat];
        }

        return $data;
    }

    /**
     * Validate the request.
     *
     * This method is called internally by gateways to avoid wasting time with an API call
     * when the request is clearly invalid.
     *
     * @param string ... a variable length list of required parameters
     * @throws InvalidRequestException
     */
    public function validate()
    {
        $this->httpRequest->request;
        foreach (func_get_args() as $key) {
            $value = $this->parameters->get($key);
            if (! isset($value)) {
                throw new InvalidRequestException("The $key parameter is required");
            }
        }
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     */
    public function sendData($data) : ResponseInterface
    {
        $this->validate('card');

        return new RedirectPurchaseResponse($this, $data);
    }

    /**
     * @param CreditCard $card
     *
     * @return string
     * @throws NumberParseException
     */
    private function formattedPhoneNumber(CreditCard $card): string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $numberProto = $phoneUtil->parse($card->getPhone(), $this->getCountry($card->getBillingCountry(), 'alpha2'));
        return $numberProto->getCountryCode()."|".str_replace(' ', '', $card->getPhone());
    }
}

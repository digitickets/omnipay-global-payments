<?php

namespace Omnipay\GlobalPayments\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use GlobalPayments\Api\Entities\Transaction;

class ApplePayPurchaseResponse extends AbstractResponse
{
    // Other codes here: https://developer.globalpay.com/resources/messages
    const SUCCESS_RESPONSE_CODE = '00';

    private $transaction = null;

    public function __construct(
        RequestInterface $request,
        Transaction $transaction
    ) {
        $this->transaction = $transaction;
        parent::__construct($request, []);
    }

    public function isSuccessful(): bool
    {
        return $this->transaction && ($this->transaction->responseCode == self::SUCCESS_RESPONSE_CODE);
    }

    public function isRedirect(): bool
    {
        return false;
    }

    public function getMessage()
    {
        return $this->transaction ? $this->transaction->responseMessage : null;
    }

    public function getCode()
    {
        return $this->transaction ? $this->transaction->responseCode : null;
    }

    public function getAuthCode()
    {
        return $this->transaction ? $this->transaction->authorizationCode : null;
    }

    /**
     * @return string|null The gateway's Transaction Ref
     */
    public function getTransactionReference()
    {
        return $this->transaction ? $this->transaction->transactionId : null;
    }

    /**
     * @return string|null Our Transaction ID
     */
    public function getTransactionId()
    {
        return $this->transaction ? $this->transaction->orderId : null;
    }

    public function getSchemeReferenceData()
    {
        return $this->transaction ? $this->transaction->schemeId : null;
    }

}

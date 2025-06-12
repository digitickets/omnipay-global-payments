<?php

namespace Omnipay\GlobalPayments\Message;

use GlobalPayments\Api\PaymentMethods\TransactionReference;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use GlobalPayments\Api\Entities\Transaction;

class VoidResponse extends AbstractResponse
{
    // https://developer.globalpay.com/resources/messages
    const SUCCESS_RESPONSE_CODE = '00';

    private $transaction = null;

    public function __construct(
        RequestInterface $request,
        Transaction $transaction = null
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

    /** Gateway's ref */
    public function getTransactionReference()
    {
        return $this->transaction->transactionId;

    }

    /** Our ref */
    public function getTransactionId()
    {
        return $this->transaction->orderId;
    }

}

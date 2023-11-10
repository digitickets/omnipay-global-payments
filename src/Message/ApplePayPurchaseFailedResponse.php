<?php

namespace Omnipay\GlobalPayments\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use GlobalPayments\Api\Entities\Transaction;

class ApplePayPurchaseFailedResponse extends AbstractResponse
{


    /**
     * @var string
     */
    private $responseMessage;

    /**
     * @var string
     */
    private $responseCode;

    /**
     * @var string
     */
    private $transactionId;

    public function __construct(
        RequestInterface $request,
        string $responseMessage,
        string $responseCode,
        string $transactionId
    ) {
        parent::__construct($request, []);
        $this->responseMessage = $responseMessage;
        $this->responseCode = $responseCode;
        $this->transactionId = $transactionId;
    }

    public function isSuccessful(): bool
    {
        return false;
    }

    public function isRedirect(): bool
    {
        return false;
    }

    public function getMessage()
    {
        return $this->responseMessage;
    }

    public function getCode()
    {
        return $this->responseCode;
    }

    public function getAuthCode()
    {
        return '';
    }

    /**
     * @return string|null The gateway's Transaction Ref
     */
    public function getTransactionReference()
    {
        return null;
    }

    /**
     * @return string|null Our Transaction ID
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }


}

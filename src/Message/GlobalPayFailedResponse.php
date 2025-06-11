<?php

namespace Omnipay\GlobalPayments\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use GlobalPayments\Api\Entities\Transaction;

class GlobalPayFailedResponse extends AbstractResponse
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

    /**
     * @var string|null
     */
    private $transactionReference = null;

    public function __construct(
        RequestInterface $request,
        string $responseMessage,
        string $responseCode,
        string $transactionId,
        string $transactionReference = null
    ) {
        parent::__construct($request, []);
        $this->responseMessage = $responseMessage;
        $this->responseCode = $responseCode;
        $this->transactionId = $transactionId;
        $this->transactionReference = $transactionReference;
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
        return $this->transactionReference;
    }

    /**
     * @return string|null Our Transaction ID
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }


}

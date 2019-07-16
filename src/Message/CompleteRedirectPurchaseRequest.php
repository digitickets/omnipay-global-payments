<?php
namespace Omnipay\GlobalPayments\Message;

use Omnipay\Common\Exception\InvalidResponseException;

class CompleteRedirectPurchaseRequest extends AbstractPurchaseRequest
{
    public const TIMESTAMP = 'TIMESTAMP';
    public const MERCHANT_ID = 'MERCHANT_ID';
    public const ORDER_ID = 'ORDER_ID';
    public const RESULT = 'RESULT';
    public const MESSAGE = 'MESSAGE';
    public const PASREF = 'PASREF';
    public const AUTHCODE = 'AUTHCODE';
    public const SHA1HASH = 'SHA1HASH';

    /**
     * This method will verify if the received information from the provider
     * is valid by comparing the recived HASH with the one we calculate locally.
     *
     * It returns all the infromation found on the provider's request
     *
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidResponseException
     */
    public function getData() : array
    {
        $hash = sha1(implode('.', [
            $this->httpRequest->request->get(static::TIMESTAMP),
            $this->httpRequest->request->get(static::MERCHANT_ID),
            $this->httpRequest->request->get(static::ORDER_ID),
            $this->httpRequest->request->get(static::RESULT),
            $this->httpRequest->request->get(static::MESSAGE),
            $this->httpRequest->request->get(static::PASREF),
            $this->httpRequest->request->get(static::AUTHCODE),
        ]));

        $hash = sha1($hash . '.' . $this->getSharedSecret());

        if ($this->httpRequest->request->get(static::SHA1HASH) !== $hash) {
            throw new InvalidResponseException;
        }

        return $this->httpRequest->request->all();
    }

    /**
     * We don't need to send anything back to the provider so we just return
     * a CompleteRedirectPurchaseResponse with the data coming from the
     * provider's request (callback)
     *
     * @param mixed $data
     *
     * @return CompleteRedirectPurchaseResponse
     */
    public function sendData($data) : CompleteRedirectPurchaseResponse
    {
        return $this->response = new CompleteRedirectPurchaseResponse(
            $this,
            $data
        );
    }
}

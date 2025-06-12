<?php

namespace Omnipay\GlobalPayments\Message;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\ServicesContainer;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractResponse;

class CompleteRedirectPurchaseRequest extends AbstractPurchaseRequest
{
    const TIMESTAMP = 'TIMESTAMP';
    const MERCHANT_ID = 'MERCHANT_ID';
    const ORDER_ID = 'ORDER_ID';
    const RESULT = 'RESULT';
    const MESSAGE = 'MESSAGE';
    const PASREF = 'PASREF';
    const AUTHCODE = 'AUTHCODE';
    const SHA1HASH = 'SHA1HASH';

    /**
     * This method will verify if the received information from the provider
     * is valid by comparing the recived HASH with the one we calculate locally.
     *
     * It returns all the infromation found on the provider's request
     *
     * @return array
     * @throws InvalidResponseException
     */
    public function getData(): array
    {
        $request = $this->httpRequest->request;
        $hash = sha1(implode('.', [
            $request->get(static::TIMESTAMP),
            $request->get(static::MERCHANT_ID),
            $request->get(static::ORDER_ID),
            $request->get(static::RESULT),
            $request->get(static::MESSAGE),
            $request->get(static::PASREF),
            $request->get(static::AUTHCODE),
        ]));

        $hash = sha1($hash.'.'.$this->getSharedSecret());

        if ($request->get(static::SHA1HASH) !== $hash) {
            throw new InvalidResponseException;
        }

        return $request->all();
    }

    /**
     * We attempt to capture the payment, and if it succeeds, we just return
     * a CompleteRedirectPurchaseResponse with the data coming from the
     * provider's request (callback)
     */
    public function sendData($data): AbstractResponse
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

        $transactionID = $data[static::PASREF];
        $orderID = $this->getTransactionId();

        $transaction = Transaction::fromId($transactionID, $orderID);

        try {
            // Note there is no "status" query endpoint, so all we can do is try and capture the payment, and see what it returns.
            $response = $transaction->capture($this->getAmount())->execute();
        } catch (GatewayException $e) {
            // The SDK throws GatewayException for any kind of non-success payment status returned by the GP API, which is annoying.
            // There are no useful codes, as most likely failure states are combined into 1 code, you have to look at the string message.
            if ($e->responseCode == 508 && strpos($e->responseMessage, "Can't settle a settled transaction") !== false) {
                // This is already settled, so all good
                $data[static::MESSAGE] .= " - Settled";

                return $this->response = new CompleteRedirectPurchaseResponse($this, $data);
            }

            return $this->response = new GlobalPayFailedResponse(
                $this,
                $e->responseMessage,
                $e->responseCode,
                $orderID,
                $transactionID
            );
        }

        $data[static::MESSAGE] .= " - ".$response->responseMessage;

        return $this->response = new CompleteRedirectPurchaseResponse($this, $data);

    }
}

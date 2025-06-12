<?php

namespace Omnipay\GlobalPayments\Message;


use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Services\ReportingService;
use Omnipay\Common\Message\ResponseInterface;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\ServicesContainer;

class VoidRequest extends AbstractPurchaseRequest
{

    public function getData(): array
    {
        return [];
    }

    /**
     * @throws GatewayException
     */
    public function sendData($data): ResponseInterface
    {
        $config = new GpEcomConfig();
        $config->merchantId = $this->getMerchantId();
        $config->accountId = $this->getAccount();
        $config->sharedSecret = $this->getSharedSecret();
        $config->refundPassword = $this->getRefundPassword();
        $config->rebatePassword = $this->getRefundPassword();
        $config->environment = Environment::PRODUCTION;
        if ($this->getTestMode()) {
            $config->environment = Environment::TEST;
        }
        ServicesContainer::configureService($config);

        // aka Pas Ref
        $globalPayTransactionID = $this->getGlobalPaymentsTransactionId();

        $transaction = Transaction::fromId($globalPayTransactionID, $this->getTransactionId());


        try {
            $responseTransaction = $transaction->void($this->getAmount())->execute();
        } catch (GatewayException $e) {
            // The SDK throws GatewayException for any kind of non-success payment status returned by the GP API, which is annoying.
            // There are no useful codes, as most likely failure states are combined into 1 code, you have to look at the string message.
            if ($e->responseCode == 508 && strpos($e->responseMessage, "been voided") !== false) {
                // This is already voided, so all good
                $transaction->responseMessage = $e->responseMessage;
                $transaction->responseCode = VoidResponse::SUCCESS_RESPONSE_CODE;

                return $this->response = new VoidResponse($this, $transaction);
            }

            return $this->response = new GlobalPayFailedResponse(
                $this,
                $e->responseMessage,
                $e->responseCode,
                $this->getTransactionId(),
                $globalPayTransactionID
            );
        }


        return $this->response = new VoidResponse(
            $this,
            $responseTransaction
        );

    }

    private function getGlobalPaymentsTransactionId()
    {
        $transactionIDOrOrderID = $this->getTransactionReference() ?: $this->getTransactionId();

        // If is in the required ID format (GP transaction ID, e.g. 17495588920222249), then we just use it as-is.
        if (is_numeric($transactionIDOrOrderID) && strlen($transactionIDOrOrderID) == 17) {
            return $transactionIDOrOrderID;
        }

        // Get the GP transactionID (aka Pas Ref) from our order ref. For some
        // undocumented reason, this accepts our ref, rather than their ref...
        try {
            /** @var Transaction $item */
            $item = ReportingService::transactionDetail($transactionIDOrOrderID)->execute();
        } catch (GatewayException $e) {
            // This order ID was not found, assume it's already a GP transaction ID, and try and use it
            return $transactionIDOrOrderID;
        }

        return $item && $item->transactionId ? $item->transactionId : $transactionIDOrOrderID;
    }

}

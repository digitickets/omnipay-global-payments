<?php

namespace Omnipay\GlobalPayments\Message;


use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Transaction;
use Omnipay\Common\Message\ResponseInterface;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\ServicesContainer;

class RefundRequest extends AbstractPurchaseRequest
{

    public function getData(): array
    {
        return [];
    }

    public function setAuthCode($value)
    {
        return $this->setParameter('authCode', $value);
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
        $config->environment = Environment::PRODUCTION;
        if ($this->getTestMode()) {
            $config->environment = Environment::TEST;
        }

        ServicesContainer::configureService($config);

        $orderId = $this->getTransactionId();
        $paymentsReference = $this->getTransactionReference();
        $authCode = $this->getAuthCode();

        $transaction = Transaction::fromId($paymentsReference, $orderId);
        $transaction->authorizationCode = $authCode;

        $refundTransaction = $transaction->refund($this->getAmountInteger())
            ->withCurrency($this->getCurrency())
            ->execute();

        return $this->response = new RefundResponse(
            $this,
            $refundTransaction
        );

    }

    public function getAuthCode()
    {
        return $this->getParameter('authCode');
    }

}

<?php

namespace Omnipay\GlobalPayments;

use function array_merge;
use Omnipay\GlobalPayments\Traits\GatewayParamsTrait;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseRequest;
use Omnipay\GlobalPayments\Message\RedirectPurchaseRequest;
use Omnipay\GlobalPayments\Message\RefundRequest;
use Omnipay\GlobalPayments\Message\ApplePayPurchaseRequest;

/**
 * @method RequestInterface completeAuthorize(array $options = [])
 * @method RequestInterface capture(array $options = [])
 * @method RequestInterface void(array $options = [])
 * @method RequestInterface createCard(array $options = [])
 * @method RequestInterface updateCard(array $options = [])
 * @method RequestInterface deleteCard(array $options = [])
 * @method RequestInterface authorize(array $options = [])
 */
class Gateway extends AbstractGateway
{
    use GatewayParamsTrait;

    public function getName(): string
    {
        return 'Global Payments';
    }

    public function getDefaultParameters(): array
    {
        return [
            'testMode' => true,
            'merchantId' => null,
            'account' => null,
            'sharedSecret' => null,
        ];
    }

    public function completePurchase(array $options = []): RequestInterface
    {
        return $this->createRequest(
            CompleteRedirectPurchaseRequest::class,
            $options
        );
    }

    /**
     * Creates either a redirect for a normal card payment, or if you pass in an applePayToken, an immediate wallet payment.
     */
    public function purchase(array $parameters = []): RequestInterface
    {
        $parameters = array_merge($this->getParameters(), $parameters);

        if (!empty($parameters["applePayToken"])) {
            return $this->createRequest(
                ApplePayPurchaseRequest::class,
                $parameters
            );
        }

        return $this->createRequest(
            RedirectPurchaseRequest::class,
            $parameters
        );
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest(RefundRequest::class, $parameters);
    }

}

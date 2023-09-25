<?php

namespace Omnipay\GlobalPayments;

use function array_merge;
use Omnipay\GlobalPayments\Traits\GatewayParamsTrait;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseRequest;
use Omnipay\GlobalPayments\Message\AuthorizeRequest;
use Omnipay\GlobalPayments\Message\RedirectPurchaseRequest;
use Omnipay\GlobalPayments\Message\RefundRequest;

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

    /**
     * Get gateway name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Global Payments';
    }

    /**
     * Get gateway default parameters
     *
     * @return array
     */
    public function getDefaultParameters(): array
    {
        return [
            'testMode' => true,
            'merchantId' => null,
            'account' => null,
            'sharedSecret' => null,
        ];
    }

    /**
     * completePuchase function to be called on provider's callback
     *
     * @param array $options
     *
     * @return RequestInterface
     */
    public function completePurchase(array $options = []): RequestInterface
    {
        return $this->createRequest(
            CompleteRedirectPurchaseRequest::class,
            $options
        );
    }

    /**
     * puchase function to be called to initiate a purchase
     *
     * @param array $parameters
     *
     * @return RequestInterface
     */
    public function purchase(array $parameters = []): RequestInterface
    {
        $parameters = array_merge($this->getParameters(), $parameters);

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

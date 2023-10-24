<?php

namespace Omnipay\GlobalPayments\Traits;

use Omnipay\Common\AbstractGateway;

/**
 * Parameters that can be set at the gateway class, and so
 * must also be available at the request message class.
 */
trait GatewayParamsTrait
{
    /**
     * getMerchantId
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->getParameter('merchantId');
    }

    /**
     * setMerchantId
     *
     * @param  [type]           $value
     *
     * @return AbstractGateway
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * getAccount
     * @return string
     */
    public function getAccount(): string
    {
        return $this->getParameter('account');
    }

    /**
     * setAccount
     *
     * @param  [type]           $value
     *
     * @return AbstractGateway
     */
    public function setAccount($value)
    {
        return $this->setParameter('account', $value);
    }

    /**
     * getChannel
     * @return string
     */
    public function getChannel(): string
    {
        return $this->getParameter('channel');
    }

    /**
     * setChannel
     *
     * @param  [type]           $value
     *
     * @return AbstractGateway
     */
    public function setChannel($value)
    {
        return $this->setParameter('channel', $value);
    }

    /**
     * getSharedSecret
     * @return string
     */
    public function getSharedSecret(): string
    {
        return $this->getParameter('sharedSecret');
    }

    /**
     * setSharedSecret
     *
     * @param  [type]           $value
     *
     * @return AbstractGateway
     */
    public function setSharedSecret($value)
    {
        return $this->setParameter('sharedSecret', $value);
    }

    public function getRefundPassword(): string
    {
        return $this->getParameter('refundPassword');
    }

    public function setRefundPassword($value)
    {
        return $this->setParameter('refundPassword', $value);
    }

}

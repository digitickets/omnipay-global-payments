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
    public function getMerchantId() : string
    {
        return $this->getParameter('merchantId');
    }

    /**
     * setMerchantId
     * @param  [type]           $value
     * @return AbstractGateway
     */
    public function setMerchantId($value) : AbstractGateway
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * getAccount
     * @return string
     */
    public function getAccount() : string
    {
        return $this->getParameter('account');
    }

    /**
     * setAccount
     * @param  [type]           $value
     * @return AbstractGateway
     */
    public function setAccount($value) : AbstractGateway
    {
        return $this->setParameter('account', $value);
    }

    /**
     * getChannel
     * @return string
     */
    public function getChannel() : string
    {
        return $this->getParameter('channel');
    }

    /**
     * setChannel
     * @param  [type]           $value
     * @return AbstractGateway
     */
    public function setChannel($value) : AbstractGateway
    {
        return $this->setParameter('channel', $value);
    }

    /**
     * getSharedSecret
     * @return string
     */
    public function getSharedSecret() : string
    {
        return $this->getParameter('sharedSecret');
    }

    /**
     * setSharedSecret
     * @param  [type]           $value
     * @return AbstractGateway
     */
    public function setSharedSecret($value) : AbstractGateway
    {
        return $this->setParameter('sharedSecret', $value);
    }

    /**
     * getRebatePass
     * @return string
     */
    public function getRebatePass() : string
    {
        return $this->getParameter('rebatePassword');
    }

    /**
     * setRebatePassword
     * @param  [type]           $value
     * @return AbstractGateway
     */
    public function setRebatePassword($value) : AbstractGateway
    {
        return $this->setParameter('rebatePassword', $value);
    }
}

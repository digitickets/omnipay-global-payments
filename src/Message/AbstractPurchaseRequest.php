<?php
namespace Omnipay\GlobalPayments\Message;

use Omnipay\Common\Message\AbstractRequest;

abstract class AbstractPurchaseRequest extends AbstractRequest
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
     * @param [type] $value
     */
    public function setMerchantId($value)
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
     * @param [type] $value
     */
    public function setAccount($value)
    {
        return $this->setParameter('account', $value);
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
     * @param [type] $value
     */
    public function setSharedSecret($value)
    {
        return $this->setParameter('sharedSecret', $value);
    }

    /**
     * getAutoSettleFlag
     * @return string
     */
    public function getAutoSettleFlag() //TODO : string
    {
        return $this->getParameter('autoSettleFlag');
    }

    /**
     * setAutoSettleFlag
     * @param [type] $value
     */
    public function setAutoSettleFlag($value)
    {
        return $this->setParameter('autoSettleFlag', $value);
    }
}

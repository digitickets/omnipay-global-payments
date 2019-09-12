<?php
namespace Omnipay\GlobalPayments\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\GlobalPayments\Traits\GatewayParamsTrait;

abstract class AbstractPurchaseRequest extends AbstractRequest
{
    use GatewayParamsTrait;
}

<?php

namespace Omnipay\GlobalPayments\Test\Message;

use Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseRequest;
use function Omnipay\GlobalPayments\Test\Gateway\getMockHttpRequest;
use Omnipay\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CompleteRedirectPurchaseResponseSuccessTest extends TestCase
{

    /** @var  \Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseResponse */
    private $response;

    /**
     * Setup
     */
    protected function setUp()
    {
        $client = $this->getHttpClient();
        $request = $this->getHttpRequest();
        $purchaseRequest = new CompleteRedirectPurchaseRequest(
            $client,
            $request
        );

        $purchaseRequest->setSharedSecret('secret');

        $this->response = $purchaseRequest->send();
    }

    /**
     * 
     */
    public function testNoRedirection()
    {
        $this->assertFalse($this->response->isRedirect());
    }

    /**
     * 
     */
    public function testSuccessfulResponse()
    {
        $this->assertTrue($this->response->isSuccessful());
    }

    /**
     * 
     */
    public function testTransactionReferencePresent()
    {
        $this->assertEquals(
            '15632757601945638',
            $this->response->getTransactionReference()
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getHttpRequest()
    {
        $requestMock = getMockHttpRequest('CompletePurchaseSuccess.txt');
        parse_str($requestMock['body'], $data);

        return new Request([], $data, [], [], [], [],
            $requestMock['body']);
    }
}
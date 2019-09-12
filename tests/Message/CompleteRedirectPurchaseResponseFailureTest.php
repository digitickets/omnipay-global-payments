<?php

namespace Omnipay\GlobalPayments\Test\Message;

use Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseRequest;
use function Omnipay\GlobalPayments\Test\Gateway\getMockHttpRequest;
use Omnipay\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CompleteRedirectPurchaseResponseFailureTest extends TestCase
{
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     */
    public function testInvalidResponse()
    {
        $request = $this->getRequestFromFile('CompletePurchaseHashFailure.txt');

        $response = $request->send();
        $response->isSuccessful();
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     */
    public function testTransactionReferenceMissing()
    {
        $request = $this->getRequestFromFile('CompletePurchaseReferenceFailure.txt');
        $response = $request->send();
        $this->assertNull(
            $response->getTransactionReference()
        );
    }

    /**
     * @param string $fixture
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequestFromFile(string $fixture)
    {
        $requestMock = getMockHttpRequest($fixture);
        parse_str($requestMock['body'], $data);

        $request = new CompleteRedirectPurchaseRequest(
            $this->getHttpClient(),
            new Request([], $data, [], [], [], [],
                $requestMock['body'])
        );

        $request->setSharedSecret('secret');

        return $request;
    }
}
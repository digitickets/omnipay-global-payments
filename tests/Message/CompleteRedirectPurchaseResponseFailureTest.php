<?php

namespace Omnipay\GlobalPayments\Test\Message;

use Omnipay\Common\Message\RequestInterface;
use Omnipay\GlobalPayments\Gateway;

use Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseRequest;
use Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseResponse;

use Omnipay\Tests\TestCase;
use ReflectionObject;
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
        $requestMock = $this->getMockHttpRequest($fixture);
        parse_str($requestMock->getBody()->getContents(), $data);

        $request = new CompleteRedirectPurchaseRequest(
            $this->getHttpClient(),
            new Request([], $data, [], [], [], [],
                $requestMock->getBody()->getContents())
        );

        $request->setSharedSecret('secret');

        return $request;

    }

    /**
     * Get a mock response for a client by mock file name
     *
     * @param string $path Relative path to the mock response file
     *
     * @return ResponseInterface
     */
    public function getMockHttpRequest($path)
    {
        if ($path instanceof RequestInterface) {
            return $path;
        }

        $ref = new ReflectionObject($this);
        $dir = dirname($ref->getFileName());

        // if mock file doesn't exist, check parent directory
        if (!file_exists($dir . '/Mock/' . $path) && file_exists($dir . '/../Mock/' . $path)) {
            return \GuzzleHttp\Psr7\parse_request(file_get_contents($dir . '/../Mock/' . $path));
        }

        return \GuzzleHttp\Psr7\parse_request(file_get_contents($dir . '/Mock/' . $path));
    }
}
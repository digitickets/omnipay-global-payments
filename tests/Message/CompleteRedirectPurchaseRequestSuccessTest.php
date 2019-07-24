<?php

namespace Omnipay\GlobalPayments\Test\Message;

use Omnipay\Common\Message\RequestInterface;

use Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseRequest;
use Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseResponse;

use Omnipay\Tests\TestCase;
use ReflectionObject;
use Symfony\Component\HttpFoundation\Request;

class CompleteRedirectPurchaseRequestSuccessTest extends TestCase
{

    /** @var  \Omnipay\GlobalPayments\Message\CompleteRedirectPurchaseRequest */
    private $request;

    /**
     * Setup
     */
    protected function setUp()
    {
        $client = $this->getHttpClient();
        $request = $this->getHttpRequest();

        $this->request = new CompleteRedirectPurchaseRequest($client, $request);
        $this->request->setSharedSecret('secret');
    }

    /**
     *
     */
    public function testGetData()
    {
        $this->assertNotNull($this->request->getData());
    }

    /**
     *
     */
    public function testCompletePurchaseSuccess()
    {
        $data = $this->request->getData();

        $this->assertEquals(
            CompleteRedirectPurchaseResponse::RESULT_SUCCESS,
            $data['RESULT']
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getHttpRequest()
    {
        $requestMock = $this->getMockHttpRequest('CompletePurchaseSuccess.txt');
        parse_str($requestMock->getBody()->getContents(), $data);

        return new Request([], $data, [], [], [], [],
            $requestMock->getBody()->getContents());
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
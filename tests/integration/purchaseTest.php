<?php

use Omnipay\GlobalPayments\Message\RedirectPurchaseRequest;
use Omnipay\GlobalPayments\Message\RedirectPurchaseResponse;
use Omnipay\Omnipay;
use PHPUnit\Framework\TestCase;

class purchaseTest extends TestCase
{

    protected $cardData = null;
    protected $credentials = null;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->cardData = [
            'number' => '4263970000005262',
            'expiryMonth' => 12,
            'expiryYear' => 22,
            'cvv' => 123,
            'billingAddress1' => 'Address 1',
            'billingAddress2' => 'Address 2',
            'billingCity' => 'London',
            'billingPostCode' => 'NW1 9PH',
            'billingState' => 'State',
            'billingCountry' => 'GB',
            'billingPhone' => 'Phone',
            'shippingAddress1' => 'Address 1',
            'shippingAddress2' => 'Address 2',
            'shippingCity' => 'London',
            'shippingPostcode' => 'NW1 9PH',
            'shippingState' => 'State',
            'shippingCountry' => 'GB',
            'shippingPhone' => 'Phone',
        ];

        $this->credentials = [
            'merchant_id' => 'totallytest',
            'account' => 'internet',
            'shared_secret' => 'secret',
        ];
    }

    /**
     * @return \Omnipay\Common\Message\RequestInterface
     */
    public function testFormData()
    {
        $gateway = Omnipay::create('GlobalPayments');

        $gateway->setTestMode(true);

        $gateway->setMerchantId($this->credentials['merchant_id']);
        $gateway->setAccount($this->credentials['account']);
        $gateway->setSharedSecret($this->credentials['shared_secret']);

        $request = $gateway->purchase([
            'amount' => 400,
            'currency' => 'EUR',
            'card' => $this->cardData,
            'returnUrl' => '/callback/api',
            'transactionId' => uniqid(),
            'description' => 'Purchase test',
        ]);

        $params = $request->getData();

        $this->assertEquals(
            40000,
            $params[RedirectPurchaseRequest::AMOUNT]
        );

        $this->assertEquals('EUR',
            $params[RedirectPurchaseRequest::CURRENCY]
        );

        $this->assertEquals(2, $params[RedirectPurchaseRequest::HPP_VERSION]);

        $this->assertEquals(
            $this->cardData['billingAddress1'],
            $params[RedirectPurchaseRequest::HPP_BILLING_STREET1]);

        $this->assertEquals(
            $this->cardData['billingAddress2'],
            $params[RedirectPurchaseRequest::HPP_BILLING_STREET2]);

        $this->assertEquals(
            $this->cardData['billingCity'],
            $params[RedirectPurchaseRequest::HPP_BILLING_CITY]);

        $this->assertEquals(
            $this->cardData['billingPostCode'],
            $params[RedirectPurchaseRequest::HPP_BILLING_POSTALCODE]);

        $this->assertEquals(
            $this->cardData['billingState'],
            $params[RedirectPurchaseRequest::HPP_BILLING_STATE]);

        $this->assertEquals('826',
            $params[RedirectPurchaseRequest::HPP_BILLING_COUNTRY]);

        $this->assertEquals(
            $this->cardData['shippingAddress1'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_STREET1]);

        $this->assertEquals(
            $this->cardData['shippingAddress2'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_STREET2]);

        $this->assertEquals(
            $this->cardData['shippingCity'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_CITY]);

        $this->assertEquals(
            $this->cardData['shippingPostcode'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_POSTALCODE]);

        $this->assertEquals(
            $this->cardData['shippingState'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_STATE]);

        $this->assertEquals(
            '826',
            $params[RedirectPurchaseRequest::HPP_SHIPPING_COUNTRY]);

        return $request;

    }

    /**
     * @depends testFormData
     *
     * @param \Omnipay\GlobalPayments\Message\RedirectPurchaseRequest $request
     *
     * @return \Omnipay\Common\Message\ResponseInterface
     */
    public function testRedirectRequest(RedirectPurchaseRequest $request)
    {

        $response = $request->send();
        $this->assertInstanceOf(RedirectPurchaseResponse::class, $response);

        return $response;

    }

    /**
     * @depends testRedirectRequest
     *
     * @param $response
     */
    public function testRedirectResponse($response)
    {
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
    }

}
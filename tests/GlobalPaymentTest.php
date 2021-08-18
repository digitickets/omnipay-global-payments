<?php

namespace Omnipay\GlobalPayments\Test\Gateway;

use Omnipay\GlobalPayments\Gateway;
use Omnipay\GlobalPayments\Message\RedirectPurchaseRequest;
use Omnipay\GlobalPayments\Message\RedirectPurchaseResponse;

use Omnipay\Tests\GatewayTestCase;

class GlobalPaymentTest extends GatewayTestCase
{
    /**
     * @var \Omnipay\GlobalPayments\Gateway
     */
    protected $gateway;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $cardData = null;

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();
        $this->gateway = new Gateway(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->options = [
            'merchantId' => 'totallytest',
            'account' => 'internet',
            'sharedSecret' => 'secret',
            'amount' => 400.00,
            'currency' => 'EUR',
            'returnUrl' => '/callback-url',
            'transactionId' => uniqid(),
        ];

        $this->cardData = [
            'number' => '4263970000005262',
            'expiryMonth' => 12,
            'expiryYear' => 22,
            'cvv' => 123,
            'email' => 'test@test.com',
            'billingAddress1' => 'Address 1',
            'billingAddress2' => 'Address 2',
            'billingCity' => 'London',
            'billingPostCode' => 'NW1 9PH',
            'billingState' => 'State',
            'billingCountry' => 'GB',
            'billingPhone' => '01293843745',
            'shippingAddress1' => 'Address 1',
            'shippingAddress2' => 'Address 2',
            'shippingCity' => 'London',
            'shippingPostcode' => 'NW1 9PH',
            'shippingState' => 'State',
            'shippingCountry' => 'GB',
            'shippingPhone' => '01293843745',
        ];

    }

    public function testPurchase()
    {
        $response = $this->gateway->purchase(
            array_merge($this->options, [
                'card' => $this->cardData,
                'description' => 'Purchase test',
            ]))->send();

        $this->assertInstanceOf(
            RedirectPurchaseResponse::class,
            $response
        );

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals(
            'https://pay.sandbox.realexpayments.com/pay',
            $response->getRedirectUrl()
        );
    }

    /**
     * @return \Omnipay\Common\Message\RequestInterface
     */
    public function testFormData()
    {
        $request = $this->gateway->purchase(
            array_merge($this->options, [
                'card' => $this->cardData,
                'transactionId' => uniqid(),
                'description' => 'Purchase test',
            ]));

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
            $params[RedirectPurchaseRequest::HPP_BILLING_STREET1]
        );

        $this->assertEquals(
            $this->cardData['billingAddress2'],
            $params[RedirectPurchaseRequest::HPP_BILLING_STREET2]
        );

        $this->assertEquals(
            $this->cardData['billingCity'],
            $params[RedirectPurchaseRequest::HPP_BILLING_CITY]
        );

        $this->assertEquals(
            $this->cardData['billingPostCode'],
            $params[RedirectPurchaseRequest::HPP_BILLING_POSTALCODE]
        );

        $this->assertEquals(
            $this->cardData['billingState'],
            $params[RedirectPurchaseRequest::HPP_BILLING_STATE]
        );

        $this->assertEquals('826',
            $params[RedirectPurchaseRequest::HPP_BILLING_COUNTRY]
        );

        $this->assertEquals(
            $this->cardData['shippingAddress1'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_STREET1]
        );

        $this->assertEquals(
            $this->cardData['shippingAddress2'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_STREET2]
        );

        $this->assertEquals(
            $this->cardData['shippingCity'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_CITY]
        );

        $this->assertEquals(
            $this->cardData['shippingPostcode'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_POSTALCODE]
        );

        $this->assertEquals(
            $this->cardData['shippingState'],
            $params[RedirectPurchaseRequest::HPP_SHIPPING_STATE]
        );

        $this->assertEquals(
            '826',
            $params[RedirectPurchaseRequest::HPP_SHIPPING_COUNTRY]
        );

        $this->assertEquals(
            'test@test.com',
            $params[RedirectPurchaseRequest::HPP_CUSTOMER_EMAIL]
        );

        $this->assertEquals(
            '44|01293843745',
            $params[RedirectPurchaseRequest::HPP_CUSTOMER_PHONENUMBER_MOBILE]
        );

        $this->assertEquals(
            false,
            $params[RedirectPurchaseRequest::HPP_ADDRESS_MATCH_INDICATOR]
        );

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

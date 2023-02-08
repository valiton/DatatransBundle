<?php

namespace Valiton\Payment\DatatransBundle\Plugin\Tests;

use JMS\Payment\CoreBundle\Entity\ExtendedData;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Valiton\Payment\DatatransBundle\Client\Client;
use Valiton\Payment\DatatransBundle\Plugin\DatatransPlugin;
use PHPUnit\Framework\TestCase;

class DatatransPluginApproveTest extends TestCase
{
    /** @var Client */
    protected $clientMock;

    /** @var DatatransPlugin */
    protected $pluginUnderTest;

    public function setUp(): void
    {
        $this->clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(["getInitUrl"])
            ->getMock();
        $this->clientMock->expects($this->any())
            ->method('getInitUrl')
            ->willReturn('www.returnUrl.com/?parameter1=1&parameter2=2');

        $loggerMock = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientMock->setLogger($loggerMock);

        $this->pluginUnderTest = new DatatransPlugin($this->clientMock, "returnUrl", "errorUrl", "cancelUrl");
        $this->pluginUnderTest->setRequestStack(new RequestStack());
    }

    public function testApproveNeedsAction()
    {
        $paymentInstruction = new PaymentInstruction(6990, 'EUR', 'datatrans', null);
        $payment = new Payment($paymentInstruction, 6990);

        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount(0);
        $financialTransaction->setRequestedAmount(6990);
        $financialTransaction->setPayment($payment);

        try {
            $this->pluginUnderTest->approve($financialTransaction, false);
            self::fail("An Exception should have been thrown");
        } catch (ActionRequiredException $exception) {
            $this->assertEquals('www.returnUrl.com/?parameter1=1&parameter2=2', $exception->getAction()->getUrl());
            $this->assertEquals($financialTransaction, $exception->getFinancialTransaction());
        }
    }

    public function testApproveWithErrorCode()
    {
        $paymentInstruction = new PaymentInstruction(6990, 'EUR', 'datatrans', null);
        $payment = new Payment($paymentInstruction, 6990);

        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount(0);
        $financialTransaction->setRequestedAmount(6990);
        $financialTransaction->setPayment($payment);

        $request = new Request([]);
        $request->request->add([
            'errorCode' => '403',
            'errorMessage' => 'BadHappening',
            'errorDetail' => 'No worries, this is just a test case.'
        ]);

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->pluginUnderTest->setRequestStack($requestStack);

        try {
            $this->pluginUnderTest->approve($financialTransaction, false);
            self::fail("An Exception should have been thrown");
        } catch (FinancialException $exception) {
            $this->assertEquals($financialTransaction, $exception->getFinancialTransaction());
            $this->assertEquals('403', $financialTransaction->getResponseCode());
            $this->assertNotEmpty($financialTransaction->getReasonCode());
        }
    }


    public function testApproveConfirmed()
    {
        $transactionId = '0123456789abcdefghi';
        $refNo = '1234';
        $amount = 69.90;
        $currency = 'EUR';

        $data = new ExtendedData();
        $data->set('refNo', '1234');
        $paymentInstruction = new PaymentInstruction($amount, $currency, 'datatrans', $data);
        $payment = new Payment($paymentInstruction, $amount);

        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount($amount);
        $financialTransaction->setRequestedAmount($amount);
        $financialTransaction->setPayment($payment);


        $request = new Request([]);
        $request->request->add([
            'responseCode' => '01',
            'reasonCode' => 'none',
            'uppTransactionId' => $transactionId,
            'amount' => (int)($amount * 100),
            'currency' => $currency,
            'refno' => $refNo
        ]);

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->pluginUnderTest->setRequestStack($requestStack);

        $this->pluginUnderTest->approve($financialTransaction, false);
        $this->assertEquals('success', $financialTransaction->getResponseCode());
        $this->assertEquals('none', $financialTransaction->getReasonCode());
        $this->assertEquals($refNo, $financialTransaction->getReferenceNumber());
        $this->assertEquals($transactionId, $financialTransaction->getTrackingId());
    }

    public function testApproveConfirmedWrongAmount()
    {
        $transactionId = '0123456789abcdefghi';
        $refNo = '1234';
        $amount = 69.90;
        $currency = 'EUR';

        $data = new ExtendedData();
        $data->set('refNo', '1234');
        $paymentInstruction = new PaymentInstruction($amount, $currency, 'datatrans', $data);
        $payment = new Payment($paymentInstruction, $amount);

        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount($amount);
        $financialTransaction->setRequestedAmount($amount);
        $financialTransaction->setPayment($payment);


        $request = new Request([]);
        $request->request->add([
            'responseCode' => '01',
            'reasonCode' => 'none',
            'uppTransactionId' => $transactionId,
            'amount' => (int)($amount * 100) * 2,
            'currency' => $currency,
            'refno' => $refNo
        ]);

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->pluginUnderTest->setRequestStack($requestStack);

        try {
            $this->pluginUnderTest->approve($financialTransaction, false);
            self::fail("An Exception should have been thrown");
        } catch (FinancialException $exception) {
            $this->assertEquals($financialTransaction, $exception->getFinancialTransaction());
            $this->assertEquals('01', $financialTransaction->getResponseCode());
            $this->assertNotEmpty($financialTransaction->getReasonCode());
        }
    }

    public function testApproveConfirmedWrongCurrency()
    {
        $transactionId = '0123456789abcdefghi';
        $refNo = '1234';
        $amount = 69.90;
        $currency = 'EUR';

        $data = new ExtendedData();
        $data->set('refNo', '1234');
        $paymentInstruction = new PaymentInstruction($amount, $currency, 'datatrans', $data);
        $payment = new Payment($paymentInstruction, $amount);

        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount($amount);
        $financialTransaction->setRequestedAmount($amount);
        $financialTransaction->setPayment($payment);


        $request = new Request([]);
        $request->request->add([
            'responseCode' => '01',
            'reasonCode' => 'none',
            'uppTransactionId' => $transactionId,
            'amount' => (int)($amount * 100),
            'currency' => 'USD',
            'refno' => $refNo
        ]);

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->pluginUnderTest->setRequestStack($requestStack);

        try {
            $this->pluginUnderTest->approve($financialTransaction, false);
            self::fail("An Exception should have been thrown");
        } catch (FinancialException $exception) {
            $this->assertEquals($financialTransaction, $exception->getFinancialTransaction());
            $this->assertEquals('01', $financialTransaction->getResponseCode());
            $this->assertNotEmpty($financialTransaction->getReasonCode());
        }
    }

    public function testApproveConfirmedWrongRefno()
    {
        $transactionId = '0123456789abcdefghi';
        $refNo = '1234';
        $amount = 69.90;
        $currency = 'EUR';

        $data = new ExtendedData();
        $data->set('refNo', '1234');
        $paymentInstruction = new PaymentInstruction($amount, $currency, 'datatrans', $data);
        $payment = new Payment($paymentInstruction, $amount);

        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount($amount);
        $financialTransaction->setRequestedAmount($amount);
        $financialTransaction->setPayment($payment);

        $request = new Request([]);
        $request->request->add([
            'responseCode' => '01',
            'reasonCode' => 'none',
            'uppTransactionId' => $transactionId,
            'amount' => (int)($amount * 100),
            'currency' => 'USD',
            'refno' => '666'
        ]);

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->pluginUnderTest->setRequestStack($requestStack);

        try {
            $this->pluginUnderTest->approve($financialTransaction, false);
            self::fail("An Exception should have been thrown");
        } catch (FinancialException $exception) {
            $this->assertEquals($financialTransaction, $exception->getFinancialTransaction());
            $this->assertEquals('01', $financialTransaction->getResponseCode());
            $this->assertNotEmpty($financialTransaction->getReasonCode());
        }
    }

    public function testApproveConfirmedBadResponse()
    {
        $transactionId = '0123456789abcdefghi';
        $refNo = '1234';
        $amount = 69.90;
        $currency = 'EUR';

        $data = new ExtendedData();
        $data->set('refNo', '1234');
        $paymentInstruction = new PaymentInstruction($amount, $currency, 'datatrans', $data);
        $payment = new Payment($paymentInstruction, $amount);

        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount($amount);
        $financialTransaction->setRequestedAmount($amount);
        $financialTransaction->setPayment($payment);

        $request = new Request([]);
        $request->request->add([
            'responseCode' => '404',
            'reasonCode' => 'none',
            'uppTransactionId' => $transactionId,
            'amount' => (int)($amount * 100),
            'currency' => $currency,
            'refno' => $refNo
        ]);

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->pluginUnderTest->setRequestStack($requestStack);

        try {
            $this->pluginUnderTest->approve($financialTransaction, false);
            self::fail("An Exception should have been thrown");
        } catch (FinancialException $exception) {
            $this->assertEquals($financialTransaction, $exception->getFinancialTransaction());
            $this->assertEquals('404', $financialTransaction->getResponseCode());
            $this->assertEquals('Invalid.', $financialTransaction->getReasonCode());
            $this->assertNotEmpty($financialTransaction->getReasonCode());
        }
    }
}

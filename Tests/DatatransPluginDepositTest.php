<?php

namespace Valiton\Payment\DatatransBundle\Plugin\Tests;

use JMS\Payment\CoreBundle\Entity\ExtendedData;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use Monolog\Logger;
use PHPUnit\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Valiton\Payment\DatatransBundle\Client\Authentication\AuthenticationStrategy;
use Valiton\Payment\DatatransBundle\Client\Client;
use Valiton\Payment\DatatransBundle\Plugin\DatatransPlugin;
use PHPUnit\Framework\TestCase;
use Valiton\Payment\DatatransBundle\Plugin\SettlementRequest;
use Valiton\Payment\DatatransBundle\Plugin\SettlementResponse;

class DatatransPluginDepositTest extends TestCase
{
    /** @var Client */
    protected $clientMock;

    /** @var DatatransPlugin */
    protected $pluginUnderTest;

    public function setUp(): void
    {
        $this->clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInitUrl', 'payComplete'])
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

    public function testDeposit()
    {
        $trackingId = '123456abcdef';
        $paymentId = '12345';
        $paymentInstruction = new PaymentInstruction(6990, 'EUR', 'datatrans', null);
        $paymentInstruction->setState(PaymentInstructionInterface::STATE_VALID);

        $payment = $this->getMockBuilder(Payment::class)
            ->setConstructorArgs([$paymentInstruction, 6990])
            ->setMethods(['getId'])
            ->getMock();
        $payment->expects($this->any())->method('getId')->willReturn($paymentId);

        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount(0);
        $financialTransaction->setRequestedAmount(6990);
        $financialTransaction->setPayment($payment);
        $financialTransaction->setTrackingId($trackingId);
        $financialTransaction->setTransactionType(FinancialTransactionInterface::TRANSACTION_TYPE_APPROVE);

        $payment->addTransaction($financialTransaction);

        $settlementRequest = new SettlementRequest();
        $settlementRequest->setUppTransactionId($trackingId);
        $settlementRequest->setAmount(699000);
        $settlementRequest->setCurrency('EUR');

        $settlementResponse = new SettlementResponse();
        $settlementResponse->setRefno($paymentId);

        $this->clientMock->expects($this->once())
            ->method('payComplete')
            ->with($settlementRequest)
            ->willReturn($settlementResponse);

        $settlementRequest->setRefno($payment->getId());

        $this->pluginUnderTest->deposit($financialTransaction, null);

        $this->assertEquals(6990, $financialTransaction->getRequestedAmount());
        $this->assertEquals($paymentId, $financialTransaction->getReferenceNumber());
        $this->assertEquals('success', $financialTransaction->getResponseCode());
    }

    public function testDepositWithError()
    {
        $trackingId = '123456abcdef';
        $paymentId = '12345';
        $paymentInstruction = new PaymentInstruction(6990, 'EUR', 'datatrans', null);
        $paymentInstruction->setState(PaymentInstructionInterface::STATE_VALID);

        $payment = $this->getMockBuilder(Payment::class)
            ->setConstructorArgs([$paymentInstruction, 6990])
            ->setMethods(['getId'])
            ->getMock();
        $payment->expects($this->any())->method('getId')->willReturn($paymentId);

        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount(0);
        $financialTransaction->setRequestedAmount(6990);
        $financialTransaction->setPayment($payment);
        $financialTransaction->setTrackingId($trackingId);
        $financialTransaction->setTransactionType(FinancialTransactionInterface::TRANSACTION_TYPE_APPROVE);

        $payment->addTransaction($financialTransaction);

        $settlementRequest = new SettlementRequest();
        $settlementRequest->setUppTransactionId($trackingId);
        $settlementRequest->setAmount(699000);
        $settlementRequest->setCurrency('EUR');

        $settlementResponse = new SettlementResponse();
        $settlementResponse->setRefno($paymentId);
        $settlementResponse->setErrorCode(404);

        $this->clientMock->expects($this->once())
            ->method('payComplete')
            ->with($settlementRequest)
            ->willReturn($settlementResponse);

        $settlementRequest->setRefno($payment->getId());

        $this->expectException(FinancialException::class);
        $this->pluginUnderTest->deposit($financialTransaction, null);

        $this->assertEquals(6990, $financialTransaction->getRequestedAmount());
        $this->assertEquals($paymentId, $financialTransaction->getReferenceNumber());
        $this->assertEquals('Fail', $financialTransaction->getResponseCode());
    }

    public function testDepositWithoutApproveTransaction()
    {
        $trackingId = '123456abcdef';
        $paymentInstruction = new PaymentInstruction(6990, 'EUR', 'datatrans', null);
        $paymentInstruction->setState(PaymentInstructionInterface::STATE_VALID);

        $payment = new Payment($paymentInstruction, 6990);


        $financialTransaction = new FinancialTransaction();
        $financialTransaction->setProcessedAmount(0);
        $financialTransaction->setRequestedAmount(6990);
        $financialTransaction->setPayment($payment);
        $financialTransaction->setTrackingId($trackingId);

        $this->expectException(FinancialException::class);
        $this->pluginUnderTest->deposit($financialTransaction, null);
    }

    public function testDepositWithErrorCode()
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
            $this->pluginUnderTest->deposit($financialTransaction, null);
            self::fail("An Exception should have been thrown");
        } catch (FinancialException $exception) {
            $this->assertEquals($financialTransaction, $exception->getFinancialTransaction());
            $this->assertEquals('Failed', $financialTransaction->getResponseCode());
            $this->assertNotEmpty($financialTransaction->getReasonCode());
        }
    }

}


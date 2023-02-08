<?php
/**
 * @package Valiton\Payment\DatatransBundle\Plugin
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 18.08.14 11:36
 */

namespace Valiton\Payment\DatatransBundle\Plugin;

use Exception;
use JMS\Payment\CoreBundle\Model\ExtendedDataInterface;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Valiton\Payment\DatatransBundle\Client\Client;


class DatatransPlugin extends AbstractPlugin
{

    const PAYMENT_SYSTEM_NAME = 'datatrans';


    /** @var Client */
    protected $client;

    /** @var string */
    protected $returnUrl;

    /** @var string */
    protected $errorUrl;

    /** @var string */
    protected $cancelUrl;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * Constructor
     *
     * @param Client $client
     * @param string $returnUrl
     * @param string $errorUrl
     * @param string $cancelUrl
     */
    public function __construct(Client $client, $returnUrl, $errorUrl, $cancelUrl)
    {
        $this->client = $client;

        $this->returnUrl = $returnUrl;
        $this->errorUrl = $errorUrl;
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * Whether this plugin can process payments for the given payment system.
     *
     * A plugin may support multiple payment systems. In these cases, the requested
     * payment system for a specific transaction  can be determined by looking at
     * the PaymentInstruction which will always be accessible either directly, or
     * indirectly.
     *
     * @param string $paymentSystemName
     * @return boolean
     */
    function processes($paymentSystemName)
    {
        return self::PAYMENT_SYSTEM_NAME === $paymentSystemName;
    }

    /**
     * Approve
     *
     * @param FinancialTransactionInterface $transaction
     * @param bool $retry
     */
    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        $payInitParameter = $this->createPayInitParameter($transaction);
        $payConfirmParameter = new PayConfirmParameter();

        $request = $this->requestStack->getMasterRequest();
        if ($request !== null
            && $request->request->has(PayConfirmParameter::PAY_PARAM_RESPONSECODE)) {

            try {
                $payConfirmParameter = $this->client->getConfirmParameter($request->request);
                $this->throwUnlessValidPayConfirm($payConfirmParameter, $payInitParameter);
            } catch (Exception $e) {
                $this->throwFinancialTransaction(
                    $transaction,
                    $e->getMessage(),
                    $request->request->get(PayConfirmParameter::PAY_PARAM_RESPONSECODE));
            }

            $transaction->setReferenceNumber($payConfirmParameter->getRefno());
            $transaction->setTrackingId($payConfirmParameter->getUppTransactionId());
            $transaction->setProcessedAmount($transaction->getRequestedAmount());
            $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
            $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);

        } elseif ($request !== null && $request->request->has(PayConfirmParameter::PAY_PARAM_ERRORCODE)) {
            $payConfirmParameter = $this->client->getConfirmParameter($request->request);
            $this->throwFinancialTransaction(
                $transaction,
                $payConfirmParameter->getError(),
                $request->request->get(PayConfirmParameter::PAY_PARAM_ERRORCODE));
        } else {
            $url = $this->client->getInitUrl($payInitParameter);

            $actionRequest = new ActionRequiredException('User has not yet authorized the transaction.');
            $actionRequest->setFinancialTransaction($transaction);
            $actionRequest->setAction(new VisitUrl($url));

            throw $actionRequest;
        }
    }


    /**
     * Deposit
     *
     * @param FinancialTransactionInterface $transaction
     * @param bool $retry
     */
    public function deposit(FinancialTransactionInterface $transaction, $retry)
    {
        try {
            $settlementRequest = $this->createSettlementRequest($transaction);
            $settlementResponse = $this->client->payComplete($settlementRequest);
            $this->throwUnlessSuccessPayComplete($settlementResponse);
        } catch (Exception $e) {
            $this->throwFinancialTransaction(
                $transaction,
                $e->getMessage(),
                isset($settlementResponse) ? $settlementResponse->getResponseCode() : null
            );
        }

        $transaction->setProcessedAmount($transaction->getRequestedAmount());
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
    }

    /**
     * Approve and deposit
     *
     * @param FinancialTransactionInterface $transaction
     * @param bool $retry
     */
    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        $this->approve($transaction, $retry);
        $this->deposit($transaction, $retry);
    }

    /**
     * Throw financial transaction
     *
     * @param FinancialTransactionInterface $transaction
     * @param $e
     * @throws FinancialException
     */

    protected function throwFinancialTransaction(FinancialTransactionInterface $transaction, $e, $responseCode)
    {
        $this->client->getLogger()->warning($e);

        $ex = new FinancialException('PaymentStatus is not completed: ' . $e);
        $ex->setFinancialTransaction($transaction);
        $transaction->setResponseCode($responseCode);
        $transaction->setReasonCode($e);

        throw $ex;
    }

    /**
     * Throw until valid payment confirmation
     *
     * @param PayConfirmParameter $payConfirmParameter
     * @param PayInitParameter $payInitParameter
     * @throws Exception
     */
    protected function throwUnlessValidPayConfirm(
        PayConfirmParameter $payConfirmParameter,
        PayInitParameter    $payInitParameter)
    {
        $valid = $payConfirmParameter->getAmount() == (string)$payInitParameter->getAmount()
            && $payConfirmParameter->getCurrency() == $payInitParameter->getCurrency()
            && $payConfirmParameter->getResponseCode() == PayConfirmParameter::PAY_PARAM_RESPONSECODE_SUCCESS;
        if (!$valid) {
            throw new Exception('Invalid.');
        }
    }

    /**
     * Throw until success payment complete response
     *
     * @param SettlementResponse $settlementResponse
     * @throws Exception
     */
    protected function throwUnlessSuccessPayComplete(SettlementResponse $settlementResponse)
    {
        $error = $settlementResponse->getErrorCode();
        if ($error !== null) {
            // Payment was not successful
            throw new Exception($settlementResponse->getErrorMessage() . ": " . $settlementResponse->getErrorDetail());
        }
    }

    /**
     * Create Settlement Request
     *
     * @param FinancialTransactionInterface $transaction
     * @return SettlementRequest
     */
    protected function createSettlementRequest(FinancialTransactionInterface $transaction)
    {
        $approveTransaction = $transaction->getPayment()->getApproveTransaction();
        if ($approveTransaction === null) {
            throw new FinancialException("Mandatory approve transaction not found.");
        }

        $referenceNumber = $approveTransaction->getTrackingId();

        /** @var PaymentInterface $payment */
        $payment = $transaction->getPayment();

        /** @var PaymentInstructionInterface $paymentInstruction */
        $paymentInstruction = $payment->getPaymentInstruction();

        $settlementRequest = new SettlementRequest();
        $settlementRequest->setUppTransactionId($referenceNumber);
        $settlementRequest->setAmount($this->formatAmount($transaction->getRequestedAmount()));
        $settlementRequest->setCurrency($paymentInstruction->getCurrency());

        $settlementRequest->setRefno($payment->getId());
        return $settlementRequest;
    }

    /**
     * Create payment init parameter
     *
     * @param FinancialTransactionInterface $transaction
     * @return PayInitParameter
     */
    protected function createPayInitParameter(FinancialTransactionInterface $transaction)
    {
        $data = $transaction->getExtendedData();

        /** @var PaymentInterface $payment */
        $payment = $transaction->getPayment();

        /** @var PaymentInstructionInterface $paymentInstruction */
        $paymentInstruction = $payment->getPaymentInstruction();

        $payInitParameter = new PayInitParameter();
        $payInitParameter->setSuccessUrl($this->getReturnUrl($data));
        $payInitParameter->setCancelUrl($this->getCancelUrl($data));
        $payInitParameter->setErrorUrl($this->getErrorUrl($data));
        $payInitParameter->setAmount($this->formatAmount($transaction->getRequestedAmount()));
        $payInitParameter->setCurrency($paymentInstruction->getCurrency());
        $payInitParameter->setRefno($payment->getId());
        return $payInitParameter;
    }

    /**
     * @param $amount
     * @param int $base
     * @return float
     */
    private function formatAmount($amount, $base = 100)
    {
        if ($amount <= 0 || $base <= 0) {
            return 0;
        }

        return $amount * $base;
    }

    /**
     * Get return url
     *
     * @param ExtendedDataInterface $data
     * @return string
     * @throws RuntimeException
     */
    private function getReturnUrl($data)
    {
        if ($data->has('return_url')) {
            return $data->get('return_url');
        } elseif (0 !== strlen($this->returnUrl)) {
            return $this->returnUrl;
        }

        throw new RuntimeException('You must configure a return url.');
    }

    /**
     * Get cancel url
     *
     * @param ExtendedDataInterface $data
     * @return string
     * @throws RuntimeException
     */
    private function getCancelUrl($data)
    {
        if ($data->has('cancel_url')) {
            return $data->get('cancel_url');
        } elseif (0 !== strlen($this->cancelUrl)) {
            return $this->cancelUrl;
        }

        throw new RuntimeException('You must configure a cancel url.');
    }

    /**
     * Get error url
     *
     * @param ExtendedDataInterface $data
     * @return string
     * @throws RuntimeException
     */
    private function getErrorUrl($data)
    {
        if ($data->has('error_url')) {
            return $data->get('error_url');
        } elseif (0 !== strlen($this->errorUrl)) {
            return $this->errorUrl;
        }

        throw new RuntimeException('You must configure an error url.');
    }

    /**
     * set request
     *
     * @param RequestStack $requestStack
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;
    }

}

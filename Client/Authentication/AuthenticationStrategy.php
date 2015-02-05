<?php
/**
 * @package Valiton\Payment\DatatransBundle\Client\Authentication
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 19.08.14 10:25
 */

namespace Valiton\Payment\DatatransBundle\Client\Authentication;

use Valiton\Payment\DatatransBundle\Client\Client;
use Valiton\Payment\DatatransBundle\Plugin\ParameterInterface;

class AuthenticationStrategy
{
    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $sign;

    /** @var string */
    protected $paymentUrl;

    /** @var string */
    protected $settlementUrl;

    /**
     * Constructor
     *
     * @param string $merchantId
     * @param string $sign
     */
    public function __construct($merchantId, $sign, $paymentUrl, $settlementUrl)
    {
        $this->merchantId = $merchantId;
        $this->sign = $sign;
        $this->paymentUrl = $paymentUrl;
        $this->settlementUrl = $settlementUrl;
    }

    /**
     * Add authentication fields
     *
     * @param ParameterInterface $parameter
     */
    public function authenticate( &$parameter, $typeUrl)
    {
        $parameter->setMerchantId($this->merchantId);
        if (!empty($this->sign)) {
            $parameter->setSign($this->sign);
        }
        if ($typeUrl === Client::PAYMENT){
            $parameter->setRequestUrl($this->paymentUrl );
        }else {
            $parameter->setRequestUrl($this->settlementUrl);
        }
        //TODO verify, that this line works correctly
      //  $parameter->setRequestUrl($typeUrl === Client::PAYMENT ? $this->paymentUrl : $this->settlementUrl);
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param string $sign
     */
    public function setSign($sign)
    {
        $this->sign = $sign;
    }

    /**
     * @param string $paymentUrl
     */
    public function setPaymentUrl($paymentUrl)
    {
        $this->paymentUrl = $paymentUrl;
    }

    /**
     * @param string $settlementUrl
     */
    public function setSettlementUrl($settlementUrl)
    {
        $this->settlementUrl = $settlementUrl;
    }


}
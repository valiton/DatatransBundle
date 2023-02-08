<?php
/**
 * @package Valiton\Payment\DatatransBundle\Plugin
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 19.08.14 14:12
 */

namespace Valiton\Payment\DatatransBundle\Plugin;

class PayInitParameter implements ParameterInterface
{
    /** @var array  */
    protected $data;

    /** @var  string */
    protected $requestUrl;


    public function __construct()
    {
        $this->data = array();
        $this->setReqtype('NOA');
    }

    public function setSign($sign)
    {
        $this->data[PayConfirmParameter::PAY_INIT_PARAM_SIGN] = $sign;
    }

    public function getSign()
    {
        return $this->data[PayConfirmParameter::PAY_INIT_PARAM_SIGN];
    }

    public function setMerchantId($merchantId)
    {
        $this->data[PayConfirmParameter::PAY_INIT_PARAM_MERCHANT_ID] = $merchantId;
    }

    public function getMerchantId()
    {
        return $this->data[PayConfirmParameter::PAY_INIT_PARAM_MERCHANT_ID];
    }

    public function setAmount($amount)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_AMOUNT] = $amount;
    }

    public function getAmount()
    {
        return $this->data[PayConfirmParameter::PAY_PARAM_AMOUNT];
    }

    public function setCurrency($currency)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_CURRENCY] = $currency;
    }

    public function getCurrency()
    {
        return $this->data[PayConfirmParameter::PAY_PARAM_CURRENCY];
    }

    public function setRefno($refno)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_REFNO] = $refno;
    }

    public function getRefno()
    {
        return $this->data[PayConfirmParameter::PAY_PARAM_REFNO];
    }

    public function setLanguage($language)
    {
        $this->data[PayConfirmParameter::PAY_INIT_PARAM_LANGUAGE] = $language;
    }

    public function getLanguage()
    {
        return $this->data[PayConfirmParameter::PAY_INIT_PARAM_LANGUAGE];
    }
    
    public function setReqtype($reqtype)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_REQTYPE] = $reqtype;
    }

    public function getReqtype()
    {
        return $this->data[PayConfirmParameter::PAY_PARAM_REQTYPE];
    }
    
    public function setSuccessUrl($successUrl)
    {
        $this->data[PayConfirmParameter::PAY_INIT_PARAM_SUCCESS_URL] = $successUrl;
    }

    public function getSuccessUrl()
    {
        return $this->data[PayConfirmParameter::PAY_INIT_PARAM_SUCCESS_URL];
    }

    public function setErrorUrl($errorUrl)
    {
        $this->data[PayConfirmParameter::PAY_INIT_PARAM_ERROR_URL] = $errorUrl;
    }

    public function getErrorUrl()
    {
        return $this->data[PayConfirmParameter::PAY_INIT_PARAM_ERROR_URL];
    }

    public function setCancelUrl($cancelUrl)
    {
        $this->data[PayConfirmParameter::PAY_INIT_PARAM_CANCEL_URL] = $cancelUrl;
    }

    public function getCancelUrl()
    {
        return $this->data[PayConfirmParameter::PAY_INIT_PARAM_CANCEL_URL];
    }

    public function setRequestUrl($requestUrl)
    {
        $this->requestUrl = $requestUrl;
    }

    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function set($field, $value)
    {
        $this->data[$field] = $value;
    }
}

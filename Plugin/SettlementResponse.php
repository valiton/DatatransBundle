<?php
/**
 * @package Valiton\Payment\DatatransBundle\Plugin
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 10.09.14 12:18
 */

namespace Valiton\Payment\DatatransBundle\Plugin;

use Doctrine\Common\Collections\ArrayCollection;
use Valiton\Payment\DatatransBundle\Client\Client;

class SettlementResponse
{
    /** @var ArrayCollection  */
    protected $data;

    public function __construct()
    {
        $this->data = new ArrayCollection();
    }

    public function setMerchantId($merchantId)
    {
        $this->data[Client::PAY_INIT_PARAM_MERCHANT_ID] = $merchantId;
    }

    public function getMerchantId()
    {
        if ($this->data->containsKey(Client::PAY_INIT_PARAM_MERCHANT_ID))
            return $this->data[Client::PAY_INIT_PARAM_MERCHANT_ID];
    }

    public function setSign($sign)
    {
        $this->data[Client::PAY_INIT_PARAM_SIGN] = $sign;
    }

    public function getSign()
    {
        if ($this->data->containsKey(Client::PAY_INIT_PARAM_SIGN))
            return $this->data[Client::PAY_INIT_PARAM_SIGN];
    }

    public function setAmount($amount)
    {
        $this->data[Client::PAY_PARAM_AMOUNT] = $amount;
    }

    public function getAmount()
    {
        if ($this->data->containsKey(Client::PAY_PARAM_AMOUNT))
            return $this->data[Client::PAY_PARAM_AMOUNT];
    }

    public function setCurrency($currency)
    {
        $this->data[Client::PAY_PARAM_CURRENCY] = $currency;
    }

    public function getCurrency()
    {
        if ($this->data->containsKey(Client::PAY_PARAM_CURRENCY))
            return $this->data[Client::PAY_PARAM_CURRENCY];
    }

    public function setRefno($refno)
    {
        $this->data[Client::PAY_PARAM_REFNO] = $refno;
    }

    public function getRefno()
    {
        if ($this->data->containsKey(Client::PAY_PARAM_REFNO))
            return $this->data[Client::PAY_PARAM_REFNO];
    }

    public function setUppTransactionId($uppTransactionId)
    {
        $this->data[Client::PAY_PARAM_UPPTRANSACTIONID] = $uppTransactionId;
    }

    public function getUppTransactionId()
    {
        if ($this->data->containsKey(Client::PAY_PARAM_UPPTRANSACTIONID))
            return $this->data[Client::PAY_PARAM_UPPTRANSACTIONID];
    }

    public function setResponseCode($responseCode)
{
    $this->data[Client::PAY_PARAM_RESPONSECODE] = $responseCode;
}

    public function getResponseCode()
    {
        if ($this->data->containsKey(Client::PAY_PARAM_RESPONSECODE))
            return $this->data[Client::PAY_PARAM_RESPONSECODE];
    }

    public function setResponseMessage($responseMessage)
    {
        $this->data[Client::PAY_PARAM_RESPONSEMESSAGE] = $responseMessage;
    }

    public function getResponseMessage()
    {
        if ($this->data->containsKey(Client::PAY_PARAM_RESPONSEMESSAGE))
            return $this->data[Client::PAY_PARAM_RESPONSEMESSAGE];
    }

    public function setErrorCode($errorCode)
    {
        $this->data[Client::PAY_PARAM_ERRORCODE] = $errorCode;
    }

    public function getErrorCode()
    {
        if ($this->data->containsKey(Client::PAY_PARAM_ERRORCODE))
            return $this->data[Client::PAY_PARAM_ERRORCODE];
    }

    public function setErrorMessage($errorMessage)
    {
        $this->data[Client::PAY_PARAM_ERRORMESSAGE] = $errorMessage;
    }

    public function getErrorMessage()
    {
        if ($this->data->containsKey(Client::PAY_PARAM_ERRORMESSAGE))
            return $this->data[Client::PAY_PARAM_ERRORMESSAGE];
    }

    public function setErrorDetail($errorDetail)
    {
        $this->data[Client::PAY_PARAM_ERRORDETAIL] = $errorDetail;
    }

    public function getErrorDetail()
    {
        if ($this->data->containsKey(Client::PAY_PARAM_ERRORDETAIL))
            return $this->data[Client::PAY_PARAM_ERRORDETAIL];
    }

    public function getError()
    {
        return $this->getErrorCode() . ', ' . $this->getErrorMessage() . ': ' . $this->getErrorDetail();
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function set($field, $value) {
        $this->data[$field] = $value;
    }

    /**
     * @return ArrayCollection
     */
    public function getData() {
        return $this->data;
    }
}
<?php
/**
 * @package Valiton\Payment\DatatransBundle\Plugin
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 10.09.14 12:18
 */

namespace Valiton\Payment\DatatransBundle\Plugin;

use Valiton\Payment\DatatransBundle\Client\Client;

class SettlementResponse
{
    /** @var array */
    protected $data;

    public function __construct()
    {
        $this->data = array();
    }

    public function setMerchantId($merchantId)
    {
        $this->data[PayConfirmParameter::PAY_INIT_PARAM_MERCHANT_ID] = $merchantId;
    }

    public function getMerchantId()
    {
        if (key_exists(PayConfirmParameter::PAY_INIT_PARAM_MERCHANT_ID, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_INIT_PARAM_MERCHANT_ID];
        }
    }

    public function setSign($sign)
    {
        $this->data[PayConfirmParameter::PAY_INIT_PARAM_SIGN] = $sign;
    }

    public function getSign()
    {
        if (key_exists(PayConfirmParameter::PAY_INIT_PARAM_SIGN, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_INIT_PARAM_SIGN];
        }
    }

    public function setAmount($amount)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_AMOUNT] = $amount;
    }

    public function getAmount()
    {
        if (key_exists(PayConfirmParameter::PAY_PARAM_AMOUNT, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_PARAM_AMOUNT];
        }
    }

    public function setCurrency($currency)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_CURRENCY] = $currency;
    }

    public function getCurrency()
    {
        if (key_exists(PayConfirmParameter::PAY_PARAM_CURRENCY, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_PARAM_CURRENCY];
        }
    }

    public function setRefno($refno)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_REFNO] = $refno;
    }

    public function getRefno()
    {
        if (key_exists(PayConfirmParameter::PAY_PARAM_REFNO, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_PARAM_REFNO];
        }
    }

    public function setUppTransactionId($uppTransactionId)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_UPPTRANSACTIONID] = $uppTransactionId;
    }

    public function getUppTransactionId()
    {
        if (key_exists(PayConfirmParameter::PAY_PARAM_UPPTRANSACTIONID, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_PARAM_UPPTRANSACTIONID];
        }
    }

    public function setResponseCode($responseCode)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_RESPONSECODE] = $responseCode;
    }

    public function getResponseCode()
    {
        if (key_exists(PayConfirmParameter::PAY_PARAM_RESPONSECODE, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_PARAM_RESPONSECODE];
        }
    }

    public function setResponseMessage($responseMessage)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_RESPONSEMESSAGE] = $responseMessage;
    }

    public function getResponseMessage()
    {
        if (key_exists(PayConfirmParameter::PAY_PARAM_RESPONSEMESSAGE, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_PARAM_RESPONSEMESSAGE];
        }
    }

    public function setErrorCode($errorCode)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_ERRORCODE] = $errorCode;
    }

    public function getErrorCode()
    {
        if (key_exists(PayConfirmParameter::PAY_PARAM_ERRORCODE, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_PARAM_ERRORCODE];
        }
    }

    public function setErrorMessage($errorMessage)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_ERRORMESSAGE] = $errorMessage;
    }

    public function getErrorMessage()
    {
        if (key_exists(PayConfirmParameter::PAY_PARAM_ERRORMESSAGE, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_PARAM_ERRORMESSAGE];
        }
    }

    public function setErrorDetail($errorDetail)
    {
        $this->data[PayConfirmParameter::PAY_PARAM_ERRORDETAIL] = $errorDetail;
    }

    public function getErrorDetail()
    {
        if (key_exists(PayConfirmParameter::PAY_PARAM_ERRORDETAIL, $this->data)) {
            return $this->data[PayConfirmParameter::PAY_PARAM_ERRORDETAIL];
        }
    }

    public function getError()
    {
        return $this->getErrorCode() . ', ' . $this->getErrorMessage() . ': ' . $this->getErrorDetail();
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function set($field, $value)
    {
        $this->data[$field] = $value;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}

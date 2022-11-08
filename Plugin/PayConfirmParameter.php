<?php

namespace Valiton\Payment\DatatransBundle\Plugin;

class PayConfirmParameter
{
    public const PAY_INIT_PARAM_CANCEL_URL = 'cancelUrl';
    public const PAY_INIT_PARAM_ERROR_URL = 'errorUrl';
    public const PAY_INIT_PARAM_LANGUAGE = 'language';
    public const PAY_INIT_PARAM_MERCHANT_ID = 'merchantId';
    public const PAY_INIT_PARAM_SIGN = 'sign';
    public const PAY_INIT_PARAM_SUCCESS_URL = 'successUrl';
    public const PAY_PARAM_AMOUNT = 'amount';
    public const PAY_PARAM_CURRENCY = 'currency';
    public const PAY_PARAM_ERRORCODE = 'errorCode';
    public const PAY_PARAM_ERRORDETAIL = 'errorDetail';
    public const PAY_PARAM_ERRORMESSAGE = 'errorMessage';
    public const PAY_PARAM_REFNO = 'refno';
    public const PAY_PARAM_REQTYPE = 'reqtype';
    public const PAY_PARAM_RESPONSECODE = 'responseCode';
    public const PAY_PARAM_RESPONSECODE_SUCCESS = 01;
    public const PAY_PARAM_RESPONSEMESSAGE = 'responseMessage';
    public const PAY_PARAM_UPPTRANSACTIONID = 'uppTransactionId';
    
    /** @var array */
    protected $data;


    public function __construct()
    {
        $this->data = array();
    }

    public function setSign($sign)
    {
        $this->data[self::PAY_INIT_PARAM_SIGN] = $sign;
    }

    public function getSign()
    {
        if (key_exists(self::PAY_INIT_PARAM_SIGN, $this->data)) {
            return $this->data[self::PAY_INIT_PARAM_SIGN];
        }
    }


    public function setMerchantId($merchantId)
    {
        $this->data[self::PAY_INIT_PARAM_MERCHANT_ID] = $merchantId;
    }

    public function getMerchantId()
    {
        if (key_exists(self::PAY_INIT_PARAM_MERCHANT_ID, $this->data)) {
            return $this->data[self::PAY_INIT_PARAM_MERCHANT_ID];
        }
    }

    public function setAmount($amount)
    {
        $this->data[self::PAY_PARAM_AMOUNT] = $amount;
    }

    public function getAmount()
    {
        if (key_exists(self::PAY_PARAM_AMOUNT, $this->data)) {
            return $this->data[self::PAY_PARAM_AMOUNT];
        }
    }

    public function setCurrency($currency)
    {
        $this->data[self::PAY_PARAM_CURRENCY] = $currency;
    }

    public function getCurrency()
    {
        if (key_exists(self::PAY_PARAM_CURRENCY, $this->data)) {
            return $this->data[self::PAY_PARAM_CURRENCY];
        }
    }

    public function setRefno($refno)
    {
        $this->data[self::PAY_PARAM_REFNO] = $refno;
    }

    public function getRefno()
    {
        if (key_exists(self::PAY_PARAM_REFNO, $this->data)) {
            return $this->data[self::PAY_PARAM_REFNO];
        }
    }

    public function setResponseCode($responseCode)
    {
        $this->data[self::PAY_PARAM_RESPONSECODE] = $responseCode;
    }

    public function getResponseCode()
    {
        if (key_exists(self::PAY_PARAM_RESPONSECODE, $this->data)) {
            return $this->data[self::PAY_PARAM_RESPONSECODE];
        }
    }

    public function setResponseMessage($responseMessage)
    {
        $this->data[self::PAY_PARAM_RESPONSEMESSAGE] = $responseMessage;
    }

    public function getResponseMessage()
    {
        if (key_exists(self::PAY_PARAM_RESPONSEMESSAGE, $this->data)) {
            return $this->data[self::PAY_PARAM_RESPONSEMESSAGE];
        }
    }

    public function setUppTransactionId($uppTransactionId)
    {
        $this->data[self::PAY_PARAM_UPPTRANSACTIONID] = $uppTransactionId;
    }

    public function getUppTransactionId()
    {
        if (key_exists(self::PAY_PARAM_UPPTRANSACTIONID, $this->data)) {
            return $this->data[self::PAY_PARAM_UPPTRANSACTIONID];
        }
    }


    public function setErrorCode($errorCode)
    {
        $this->data[self::PAY_PARAM_ERRORCODE] = $errorCode;
    }

    public function getErrorCode()
    {
        if (key_exists(self::PAY_PARAM_ERRORCODE, $this->data)) {
            return $this->data[self::PAY_PARAM_ERRORCODE];
        }
    }

    public function setErrorMessage($errorMessage)
    {
        $this->data[self::PAY_PARAM_ERRORMESSAGE] = $errorMessage;
    }

    public function getErrorMessage()
    {
        if (key_exists(self::PAY_PARAM_ERRORMESSAGE, $this->data)) {
            return $this->data[self::PAY_PARAM_ERRORMESSAGE];
        }
    }

    public function setErrorDetail($errorDetail)
    {
        $this->data[self::PAY_PARAM_ERRORDETAIL] = $errorDetail;
    }

    public function getErrorDetail()
    {
        if (key_exists(self::PAY_PARAM_ERRORDETAIL, $this->data)) {
            return $this->data[self::PAY_PARAM_ERRORDETAIL];
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

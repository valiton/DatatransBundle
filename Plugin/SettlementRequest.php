<?php
/**
 * @package Valiton\Payment\DatatransBundle\Plugin
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 10.09.14 12:17
 */

namespace Valiton\Payment\DatatransBundle\Plugin;
use Valiton\Payment\DatatransBundle\Client\Client;

class SettlementRequest implements ParameterInterface
{
    const KEY_ATTRIBUTES = '@attributes';
    const KEY_VALUE = 'version';
    const KEY_BODY = 'body';
    const KEY_TRANSACTION = 'transaction';
    const KEY_REQUEST = 'request';

    /** @var array  */
    protected $data;

    /** @var  string */
    protected $requestUrl;

    public function __construct()
    {
        $this->data = [
            self::KEY_ATTRIBUTES => [self::KEY_VALUE => '1'],
            self::KEY_BODY => [
                self::KEY_ATTRIBUTES => [],
                self::KEY_TRANSACTION => [
                    self::KEY_ATTRIBUTES => [],
                    self::KEY_REQUEST =>[]
                ]
            ]
        ];
    }

    public function setSign($sign)
    {
        $this->data[self::KEY_BODY][self::KEY_ATTRIBUTES][PayConfirmParameter::PAY_INIT_PARAM_SIGN] = $sign;
    }

    public function getSign()
    {
        $request = $this->data[self::KEY_BODY][self::KEY_ATTRIBUTES];
        if (key_exists(PayConfirmParameter::PAY_INIT_PARAM_SIGN, $request))
            return $request[PayConfirmParameter::PAY_INIT_PARAM_SIGN];
    }

    public function setMerchantId($merchantId)
    {
        $this->data[self::KEY_BODY][self::KEY_ATTRIBUTES][PayConfirmParameter::PAY_INIT_PARAM_MERCHANT_ID] = $merchantId;
    }

    public function getMerchantId()
    {
        $request = $this->data[self::KEY_BODY][self::KEY_ATTRIBUTES];
        if (key_exists(PayConfirmParameter::PAY_INIT_PARAM_MERCHANT_ID, $request))
            return $request[PayConfirmParameter::PAY_INIT_PARAM_MERCHANT_ID];
    }

    public function setAmount($amount)
    {
        $this->data[self::KEY_BODY][self::KEY_TRANSACTION][self::KEY_REQUEST][PayConfirmParameter::PAY_PARAM_AMOUNT] = $amount;
    }

    public function getAmount()
    {
        $request = $this->data[self::KEY_BODY][self::KEY_TRANSACTION][self::KEY_REQUEST];
        if (key_exists(PayConfirmParameter::PAY_PARAM_AMOUNT, $request))
            return $request[PayConfirmParameter::PAY_PARAM_AMOUNT];
    }

    public function setCurrency($currency)
    {
        $this->data[self::KEY_BODY][self::KEY_TRANSACTION][self::KEY_REQUEST][PayConfirmParameter::PAY_PARAM_CURRENCY] = $currency;
    }

    public function getCurrency()
    {
        $request = $this->data[self::KEY_BODY][self::KEY_TRANSACTION][self::KEY_REQUEST];
        if (key_exists(PayConfirmParameter::PAY_PARAM_CURRENCY, $request))
            return $request[PayConfirmParameter::PAY_PARAM_CURRENCY];
    }

    public function setRefno($refno)
    {
        $this->data[self::KEY_BODY][self::KEY_TRANSACTION][self::KEY_ATTRIBUTES][PayConfirmParameter::PAY_PARAM_REFNO] = $refno;
    }

    public function getRefno()
    {
        $request = $this->data[self::KEY_BODY][self::KEY_TRANSACTION][self::KEY_ATTRIBUTES];
        if (key_exists(PayConfirmParameter::PAY_PARAM_REFNO, $request))
            return $request[PayConfirmParameter::PAY_PARAM_REFNO];
    }

    public function setUppTransactionId($uppTransactionId)
    {
        $this->data[self::KEY_BODY][self::KEY_TRANSACTION][self::KEY_REQUEST][PayConfirmParameter::PAY_PARAM_UPPTRANSACTIONID] = $uppTransactionId;
    }

    public function getUppTransactionId()
    {
        $request = $this->data[self::KEY_BODY][self::KEY_TRANSACTION][self::KEY_REQUEST];
        if (key_exists(PayConfirmParameter::PAY_PARAM_UPPTRANSACTIONID, $request))
            return $request[PayConfirmParameter::PAY_PARAM_UPPTRANSACTIONID];
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
     * @param string $field
     * @param string $value
     */
    public function set($field, $value) {
        $this->data[$field] = $value;
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }

}
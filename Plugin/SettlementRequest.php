<?php
/**
 * @package Valiton\Payment\DatatransBundle\Plugin
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 10.09.14 12:17
 */

namespace Valiton\Payment\DatatransBundle\Plugin;
use Doctrine\Common\Collections\ArrayCollection;
use Valiton\Payment\DatatransBundle\Client\Client;
use Valiton\Payment\DatatransBundle\Plugin\DatatransPlugin;
use Valiton\Payment\DatatransBundle\Plugin\PayConfirmParameter;


class SettlementRequest implements ParameterInterface
{
    /** @var array  */
    protected $data;

    /** @var  string */
    protected $requestUrl;

    public function __construct()
    {
        $this->data = new ArrayCollection();
        $this->data = array(
            '@attributes' => array(
                'version' => '1'
            ),
            'body' => array(
                '@attributes' => array(
                ),
                'transaction' => array(
                    '@attributes' => array(
                    ),
                    'request' => array(
                    )
                )

            )
        );

    }

    public function setSign($sign)
    {
        $this->data['body']['@attributes'][Client::PAY_INIT_PARAM_SIGN] = $sign;
    }

    public function getSign()
    {
        $request = $this->data['body']['@attributes'];
        if ($request->containsKey(Client::PAY_INIT_PARAM_SIGN))
            return $request[Client::PAY_INIT_PARAM_SIGN];
    }

    public function setMerchantId($merchantId)
    {
        $this->data['body']['@attributes'][Client::PAY_INIT_PARAM_MERCHANT_ID] = $merchantId;
    }

    public function getMerchantId()
    {
        $request = $this->data['body']['@attributes'];
        if ($request->containsKey(Client::PAY_INIT_PARAM_MERCHANT_ID))
            return $request[Client::PAY_INIT_PARAM_MERCHANT_ID];
    }

    public function setAmount($amount)
    {
        $this->data['body']['transaction']['request'][Client::PAY_PARAM_AMOUNT] = $amount;
    }

    public function getAmount()
    {
        $request = $this->data['body']['transaction']['request'];
        if ($request->containsKey(Client::PAY_PARAM_AMOUNT))
            return $request[Client::PAY_PARAM_AMOUNT];
    }

    public function setCurrency($currency)
    {
        $this->data['body']['transaction']['request'][Client::PAY_PARAM_CURRENCY] = $currency;
    }

    public function getCurrency()
    {
        $request = $this->data['body']['transaction']['request'];
        if ($request->containsKey(Client::PAY_PARAM_CURRENCY))
            return $request[Client::PAY_PARAM_CURRENCY];
    }

    public function setRefno($refno)
    {
        $this->data['body']['transaction']['@attributes'][Client::PAY_PARAM_REFNO] = $refno;
    }

    public function getRefno()
    {
        $request = $this->data['body']['transaction']['@attributes'];
        if ($request->containsKey(Client::PAY_PARAM_REFNO))
            return $request[Client::PAY_PARAM_REFNO];
    }

    public function setUppTransactionId($uppTransactionId)
    {
        $this->data['body']['transaction']['request'][Client::PAY_PARAM_UPPTRANSACTIONID] = $uppTransactionId;
    }

    public function getUppTransactionId()
    {
        $request = $this->data['body']['transaction']['request'];
        if ($request->containsKey(Client::PAY_PARAM_UPPTRANSACTIONID))
            return $request[Client::PAY_PARAM_UPPTRANSACTIONID];
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
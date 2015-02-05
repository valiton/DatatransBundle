<?php
/**
 * @package Valiton\Payment\DatatransBundle\Plugin
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 11.09.14 09:55
 */

namespace Valiton\Payment\DatatransBundle\Plugin;


interface ParameterInterface {

    public function getMerchantId();
    public function setMerchantId($merchantId);

    public function setSign($sign);
    public function getSign();

    public function setAmount($amount);
    public function getAmount();

    public function setCurrency($currency);
    public function getCurrency();

    public function setRefno($refno);
    public function getRefno();

    public function getData();

    public function set($field, $value);

    public function getRequestUrl();
    public function setRequestUrl($requestUrl);

}
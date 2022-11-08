<?php

namespace Valiton\Payment\DatatransBundle\Client\Authentication;

use Valiton\Payment\DatatransBundle\Plugin\ParameterInterface;

class AuthenticationStrategy
{
    /** @var string */
    protected $merchantId;

    /** @var string */
    protected $sign;

    /** @var string */
    protected $httpAuthUserPwd;

    /** @var string */
    protected $hmacKey;


    /** @var string */
    protected $hmacKeyBin;

    /** @var string */
    protected $paymentUrl;

    /** @var string */
    protected $settlementUrl;

    const PAYMENT = 'payment';
    const SETTLEMENT = 'settlement';

    /**
     * Constructor
     *
     * @param string $merchantId
     * @param string $sign
     * @param string $paymentUrl
     * @param string $settlementUrl
     */
    public function __construct($merchantId, $sign, $hmacKey, $paymentUrl, $settlementUrl)
    {
        $this->merchantId = $merchantId;
        $this->sign = $sign;
        $this->paymentUrl = $paymentUrl;
        $this->settlementUrl = $settlementUrl;
        $this->hmacKeyBin = hex2bin($hmacKey);
    }
    /**
     * Add authentication fields
     *
     * @param ParameterInterface $parameter
     */
    public function authenticate(&$parameter, $typeUrl)
    {
        $parameter->setMerchantId($this->merchantId);
        $parameter->setSign($this->getHexaSHA256Signature($parameter));

        if ($typeUrl === self::PAYMENT) {
            $parameter->setRequestUrl($this->paymentUrl);
        } elseif ($typeUrl === self::SETTLEMENT) {
            $parameter->setRequestUrl($this->settlementUrl);
        } else {
            throw new \Exception("Unknown url type provided for authentication: " . $typeUrl);
        }
    }

    public function getHexaSHA256Signature(ParameterInterface $parameter)
    {
        $string = $this->merchantId .
            $parameter->getAmount() .
            $parameter->getCurrency() .
            $parameter->getRefno();

        return hash_hmac('sha256', $string, $this->hmacKeyBin, false);
    }

    public function getHttpAuthUserPwd()
    {
        if ($this->httpAuthUserPwd == null) {
            $this->httpAuthUserPwd = $this->merchantId . ':' . $this->sign;
        }

        return $this->httpAuthUserPwd;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param string $sign
     */
    public function setSign(string $sign): void
    {
        $this->sign = $sign;
    }

    /**
     * @param string $httpAuthUserPwd
     */
    public function setHttpAuthUserPwd(string $httpAuthUserPwd): void
    {
        $this->httpAuthUserPwd = $httpAuthUserPwd;
    }

    /**
     * @param string $hmacKey
     */
    public function setHmacKey(string $hmacKey): void
    {
        $this->hmacKey = $hmacKey;
        $this->hmacKeyBin = hex2bin($hmacKey);
    }

    /**
     * @param string $paymentUrl
     */
    public function setPaymentUrl(string $paymentUrl): void
    {
        $this->paymentUrl = $paymentUrl;
    }

    /**
     * @param string $settlementUrl
     */
    public function setSettlementUrl(string $settlementUrl): void
    {
        $this->settlementUrl = $settlementUrl;
    }
}


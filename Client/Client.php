<?php
/**
 * @package Valiton\Payment\DatatransBundle\Client
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 18.08.14 11:41
 */

namespace Valiton\Payment\DatatransBundle\Client;

use Psr\Log\LoggerInterface;
use Valiton\Payment\DatatransBundle\Plugin\PayConfirmParameter;
use Valiton\Payment\DatatransBundle\Client\Authentication\AuthenticationStrategy;
use Valiton\Payment\DatatransBundle\Plugin\SettlementRequest;
use Valiton\Payment\DatatransBundle\Plugin\SettlementResponse;
use JMS\Payment\CoreBundle\BrowserKit\Request;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;
use Symfony\Component\BrowserKit\Response as RawResponse;
use Valiton\Payment\DatatransBundle\Plugin\ParameterInterface;
use Valiton\Payment\DatatransBundle\Utils\ArrayToXml;

class Client {
    const PAY_INIT_PARAM_MERCHANT_ID = 'merchantId';
    const PAY_INIT_PARAM_SIGN = 'sign';
    const PAY_INIT_PARAM_SUCCESS_URL = 'successUrl';
    const PAY_INIT_PARAM_ERROR_URL = 'errorUrl';
    const PAY_INIT_PARAM_CANCEL_URL = 'cancelUrl';
    const PAY_INIT_PARAM_LANGUAGE = 'language';
    const PAY_PARAM_AMOUNT = 'amount';
    const PAY_PARAM_CURRENCY = 'currency';
    const PAY_PARAM_REFNO = 'refno';
    const PAY_PARAM_RESPONSECODE = "responseCode";
    const PAY_PARAM_RESPONSEMESSAGE = "responseMessage";
    const PAY_PARAM_UPPTRANSACTIONID = "uppTransactionId";
    const PAY_PARAM_ERRORCODE = "errorCode";
    const PAY_PARAM_ERRORMESSAGE = "errorMessage";
    const PAY_PARAM_ERRORDETAIL = "errorDetail";
    const PAY_PARAM_REQTYPE = "reqtype";

    const PAYMENT = 'payment';
    const SETTLEMENT = 'settlement';
    /**
     * @var AuthenticationStrategy
     */
    protected $authenticationStrategy;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param AuthenticationStrategy $authenticationStrategy
     */
    public function __construct(AuthenticationStrategy $authenticationStrategy)
    {
        $this->authenticationStrategy = $authenticationStrategy;
    }

    /**
     * get payment Confirm Parameters
     * @param $request
     * @return PayConfirmParameter
     */
    public function getConfirmParameter($request)
    {
        $payConfirmParameter = new PayConfirmParameter();
        foreach($request as $field => $value) {
            $payConfirmParameter->set($field, $value);
        }
        echo $payConfirmParameter->getResponseCode();
        $this->logger->info("Response:\n" . var_export($payConfirmParameter->getData(), true));
        return $payConfirmParameter;
    }


    /**
      * Create payment init url
     * @param string $url
     * @param ParameterInterface $parameter
     * @return string
     */
    public function getInitUrl($parameter){
        $this->authenticationStrategy->authenticate($parameter, self::PAYMENT);
        $par = http_build_query($parameter->getData());
        $this->logger->info($parameter->getRequestUrl());
        $this->logger->info("Request:\n" . var_export($parameter->getData(), true));
        return $parameter->getRequestUrl() . "?" . $par;
    }

    /**
     * Pay complete
     * @param  SettlementRequest $settlementRequest
     * @return SettlementResponse
     * @throws \Exception
     */
    public function payComplete(SettlementRequest $settlementRequest)
    {
        $settlementResponse = new SettlementResponse();

        $response = $this->sendApiRequest($settlementRequest);

        $this->fillDataFromXML($settlementResponse, $response);

        return $settlementResponse;
    }

    /**
     * Fill XML from data
     *
     * @param ParameterInterface $settlementRequest
     * @param $xml
     */
    private function fillXMLFromData($settlementRequest)
    {
        return ArrayToXml::toXML($settlementRequest, 'paymentService');
    }

    /**
     * Fill data from XML
     *
     * @param SettlementResponse $settlementResponse
     * @param $xml
     */
    protected function fillDataFromXML(SettlementResponse $settlementResponse, $xml)
    {
        $p = xml_parser_create();
        xml_parse_into_struct($p, $xml, $values, $index);
        foreach ($values as $value) {
            if ($value['type'] == 'complete'){
                $constant = "self::PAY_PARAM_" . trim($value['tag']);
                $settlementResponse->set(
                    !defined($constant) ? mb_strtolower(trim($value['tag'])) : constant($constant),
                    $value['value']
                );
            }
        }
    }

    /**
     * Send api request
     *
     * @param $url
     * @param ParameterInterface $parameter
     * @return mixed
     * @throws \Exception
     */
    public function sendApiRequest($parameter)
    {
        $this->authenticationStrategy->authenticate($parameter, self::SETTLEMENT);
        $this->logger->info($parameter->getRequestUrl());
        $this->logger->info("Request:\n" . var_export($parameter->getData(), true));
        $data = array('xmlRequest' => $this->fillXMLFromData($parameter->getData()));

        $request = new Request(
            $parameter->getRequestUrl(),
            'POST',
            $data
        );
        $response = $this->request($request);

        $this->logger->info("Response:\n" . $response->getContent());

        if ($response->getStatus() != 200) {
            $this->logger->critical('Datatrans: request failed with statuscode: {statuscode}!', array('statuscode' => $response->getStatusCode()));
            throw new \Exception('Datatrans: request failed with statuscode: ' . $response->getStatus() . '!');
        }
        return $response->getContent();
    }

    public function request(Request $request)
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('The cURL extension must be loaded.');
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $request->getUri());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        // add headers
        $headers = array();
        foreach ($request->headers->all() as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $subValue) {
                    $headers[] = sprintf('%s: %s', $name, $subValue);
                }
            } else {
                $headers[] = sprintf('%s: %s', $name, $value);
            }
        }
        if (count($headers) > 0) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        // set method
        $method = strtoupper($request->getMethod());
        if ('POST' === $method) {
            curl_setopt($curl, CURLOPT_POST, true);
            if (!$request->headers->has('Content-Type') || 'multipart/form-data' !== $request->headers->get('Content-Type')) {
                $postFields = http_build_query($request->request->all());
            } else {
                $postFields = $request->request->all();
            }

            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        }

        // perform the request
        if (false === $returnTransfer = curl_exec($curl)) {
            throw new CommunicationException(
                'cURL Error: '.curl_error($curl), curl_errno($curl)
            );
        }

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = array();
        if (preg_match_all('#^([^:\r\n]+):\s+([^\n\r]+)#m', substr($returnTransfer, 0, $headerSize), $matches)) {
            foreach ($matches[1] as $key => $name) {
                $headers[$name] = $matches[2][$key];
            }
        }

        $response = new RawResponse(
            substr($returnTransfer, $headerSize),
            curl_getinfo($curl, CURLINFO_HTTP_CODE),
            $headers
        );
        curl_close($curl);

        return $response;
    }

    /**
     * get logger
     *
     * @return \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * set logger
     *
     * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
} 
<?php

namespace Valiton\Payment\DatatransBundle\Client;

use Exception;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\BrowserKit\Response as RawResponse;
use Valiton\Payment\DatatransBundle\Client\Authentication\AuthenticationStrategy;
use Valiton\Payment\DatatransBundle\Plugin\ParameterInterface;
use Valiton\Payment\DatatransBundle\Plugin\PayConfirmParameter;
use Valiton\Payment\DatatransBundle\Plugin\SettlementRequest;
use Valiton\Payment\DatatransBundle\Plugin\SettlementResponse;
use Valiton\Payment\DatatransBundle\Utils\ArrayToXml;

class Client {

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
        $this->logger->info("Response:\n" . var_export($payConfirmParameter->getData(), true));
        return $payConfirmParameter;
    }

    /**
     * Create payment init url
     * @param ParameterInterface $parameter
     * @return string
     */
    public function getInitUrl($parameter)
    {
        $this->authenticationStrategy->authenticate($parameter, AuthenticationStrategy::PAYMENT);
        $httpParameter = http_build_query($parameter->getData());

        $this->logger->info($parameter->getRequestUrl());
        $this->logger->info("Request:\n" . var_export($parameter->getData(), true));

        return $parameter->getRequestUrl() . "?" . $httpParameter;
    }

    /**
     * Pay complete
     * @param  SettlementRequest $settlementRequest
     * @return SettlementResponse
     * @throws Exception
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
     * @return string
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
     * @param ParameterInterface $parameter
     * @return mixed
     * @throws Exception
     */
    public function sendApiRequest(ParameterInterface $parameter)
    {
        $this->authenticationStrategy->authenticate($parameter, AuthenticationStrategy::SETTLEMENT);

        $response = $this->sendRequest($parameter);

        if ($response->getStatus() != 200) {
            $this->logger->critical('Datatrans: request failed with statuscode: {statuscode}!', ['statuscode' => $response->getStatusCode()]);
            throw new Exception('Datatrans: request failed with statuscode: ' . $response->getStatus());
        }

        return $response->getContent();
    }

    public function sendRequest(ParameterInterface $parameter)
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('The cURL extension must be loaded.');
        }

        $data = array('xmlRequest' => $this->fillXMLFromData($parameter->getData()));

        $this->logger->info($parameter->getRequestUrl());
        $this->logger->info("Request:\n" . var_export($data, true));

        $curlOpts = [
            CURLOPT_URL => $parameter->getRequestUrl(),
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->authenticationStrategy->getHttpAuthUserPwd(),
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $curlOpts);
        $returnTransfer = curl_exec($curl);

        if (!$returnTransfer) {
            $this->logger->error(curl_error($curl));
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

        $this->logger->info("Response: " . $response->getContent());

        return $response;
    }

    /**
     * get logger
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}

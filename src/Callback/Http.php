<?php

namespace zaboy\scheduler\Callback;

use zaboy\scheduler\Callback\Interfaces\CallbackInterface;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;

class Http implements CallbackInterface
{
    const DEFAULT_REQUEST_TYPE = Request::METHOD_GET;

    protected $url;

    protected $requestOptions = [];

    protected $login;

    protected $password;

    protected $requestMethod;

    protected $requestHeaders = [];

    public function __construct(array $params = [])
    {
        // The parameters of request: url and options for request (f.e. timeout)
        if (!isset($params['url'])) {
            throw new CallbackException("The necessary parameter \"url\" is expected");
        }
        $this->url = $params['url'];

        $supportedKeys = [
            'login',
            'password',
            'maxredirects',
            'useragent',
            'timeout',
        ];
        if (isset($params['requestOptions'])) {
            $requestOptions = array_intersect_key($params['requestOptions'], array_flip($supportedKeys));
            if (isset($requestOptions['login'])) {
                $this->login = $requestOptions['login'];
                unset($requestOptions['login']);
            }
            if (isset($requestOptions['password'])) {
                $this->login = $requestOptions['password'];
                unset($requestOptions['password']);
            }
        }
        if (!isset($params['method'])) {
            $params['method'] = self::DEFAULT_REQUEST_TYPE;
        }
        $this->requestMethod = $params['method'];
        if (isset($params['headers'])) {
            $this->requestHeaders = (array) $params['headers'];
        }
    }

    public function call(array $options = [])
    {
        /** @var Client $httpClient */
        $httpClient = $this->initHttpClient();
        $httpClient->setRawBody($this->jsonEncode($options));
        $response = $httpClient->send();
        if ($response->isSuccess()) {
            $result = $this->jsonDecode($response->getBody());
        } else {
            throw new CallbackException(
                'Status: ' . $response->getStatusCode()
                . ' - ' . $response->getReasonPhrase()
            );
        }
        return $result;
    }

    /**
     * @return Client
     */
    protected function initHttpClient()
    {
        $httpClient = new Client($this->url);
        if (isset($this->login) && isset($this->password)) {
            $httpClient->setAuth($this->login, $this->password);
        }
        $httpClient->setHeaders($this->requestHeaders);
        $httpClient->setMethod($this->requestMethod);
        return $httpClient;
    }


    protected function jsonEncode($data)
    {
        json_encode(null); // Clear json_last_error()
        $result = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $jsonErrorMsg = json_last_error_msg();
            json_encode(null);  // Clear json_last_error()
            throw new CallbackException(
                'Unable to encode data to JSON - ' . $jsonErrorMsg
            );
        }
        return $result;
    }

    protected function jsonDecode($data)
    {
        json_encode(null); // Clear json_last_error()
        $result = Json::decode($data, Json::TYPE_ARRAY); //json_decode($data);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $jsonErrorMsg = json_last_error_msg();
            json_encode(null);  // Clear json_last_error()
            throw new CallbackException(
                'Unable to decode data from JSON - ' . $jsonErrorMsg
            );
        }
        return $result;
    }
}
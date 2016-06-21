<?php

namespace zaboy\scheduler\Callback;

use zaboy\scheduler\Callback\Interfaces\CallbackInterface;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;

class Http implements CallbackInterface
{
    /**
     * Request method by default
     */
    const DEFAULT_REQUEST_METHOD = Request::METHOD_GET;

    /**
     * URL to send request
     *
     * Must be real url, f.e. http://some.domen.org
     *
     * @var string url
     */
    protected $url;

    /**
     * Options which will be passed to request
     *
     * @var array
     */
    protected $requestOptions = [];

    /**
     * Login for http-authorization if need
     *
     * @var string
     */
    protected $login;

    /**
     * Password for http-authorization if need
     *
     * @var string
     */
    protected $password;

    /**
     * Method of request
     *
     * Valid values may be found in Zend\Http\Request
     *
     * @see Zend\Http\Request
     * @var string
     */
    protected $requestMethod;

    /**
     * Headers which will be passed to request
     *
     * @var array
     */
    protected $requestHeaders = [];

    /**
     * {@inherit}
     *
     * {@inherit}
     */
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
            $params['method'] = self::DEFAULT_REQUEST_METHOD;
        }
        $this->requestMethod = $params['method'];
        if (isset($params['headers'])) {
            $this->requestHeaders = (array) $params['headers'];
        }
    }

    /**
     * {@inherit}
     *
     * {@inherit}
     */
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
     * Creates and configures a http client
     *
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

    /**
     * Encodes $data to json string
     *
     * TODO: Встречается в нескольких местах, требуется рефакторинг
     *
     * @param $data
     * @return string
     * @throws CallbackException
     */
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

    /**
     * Decodes json string to array or object
     *
     * TODO: Встречается в нескольких местах, требуется рефакторинг
     *
     * @param $data
     * @return mixed
     * @throws CallbackException
     */
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
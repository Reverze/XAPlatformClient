<?php
/**
 * @Author Reverze (hawkmedia24@gmail.com)
 *
 * This module is part of XAPlatformClient.
 *
 *
 */

namespace XA\PlatformClient\Rest;

use Circle\RestClientBundle\Services\RestClient as CircleRestClient;
use Circle\RestClientBundle\Services\Curl;
use Circle\RestClientBundle\Services\CurlOptionsHandler;
use Sway\Component\Http\Request;
use XA\PlatformClient\Auth\PlatformCredentials;
use Symfony\Component\HttpFoundation\Response;
use XA\PlatformClient\Request\Exception\RequestParameterException;
use XA\PlatformClient\Request\GlobalRequest;
use XA\PlatformClient\Rest\Exception\RestException;
use XA\PlatformClient\Routing\RouteDefinitions;
use XA\PlatformClient\Request\RequestDefinitions;


class RestClient
{
    /**
     * Curl configuration
     * @var \XA\PlatformClient\Rest\CurlConfiguration
     */
    private $curlConfiguration = null;

    /**
     * Defaults http headers
     * @var array
     */
    private $defaultHeaders = array();

    /**
     * Curl options handler
     * @var \Circle\RestClientBundle\Services\CurlOptionsHandler
     */
    private $curlOptionsHandler = null;

    /**
     * Curl
     * @var \Circle\RestClientBundle\Services\Curl
     */
    private $curl = null;

    /**
     * Circle's REST client
     * @var \Circle\RestClientBundle\Services\RestClient
     */
    private $client = null;

    /**
     * @var Provider's uri
     */
    private $uri = null;

    public function __construct(PlatformCredentials $providerCredentials)
    {
        if (empty($this->curlConfiguration)){
            $this->curlConfiguration = new CurlConfiguration();
        }

        $this->useCredentials($providerCredentials);

        $this->initialize();
    }

    /**
     * Sets http header
     * @param string $headerName
     * @param string $value
     */
    public function setHeader(string $headerName, string $value)
    {
        $this->curlConfiguration->setHTTPHeader($headerName, $value);
    }

    /**
     * Loads credentials
     * @param PlatformCredentials $providerCredentials
     */
    private function useCredentials(PlatformCredentials $providerCredentials)
    {
        $credentials = $providerCredentials->getCredentials();

        $this->setHeader('ax-platform-app', $credentials['appKey']);

        $protocol = 'http';

        if (strpos($credentials['providerHost'], 'https') !== false){
            $protocol = 'https';
        }

        $credentials['providerHost'] = str_replace("https://", "", $credentials['providerHost']);

        /**
         * Compiles provider uri
         */
        $this->uri = sprintf("%s://%s:%d", $protocol, $credentials['providerHost'], $credentials['providerPort']);
    }

    /**
     * Initializes REST client
     */
    private function initialize()
    {
        /**
         * Initialize curl options handler with defaults options
         */
        $this->curlOptionsHandler = new CurlOptionsHandler($this->curlConfiguration->getCurlDefaults());

        /**
         * Initializes curl wrapper
         */
        $this->curl = new Curl($this->curlOptionsHandler);

        /**
         * Initializes circle's REST client
         */
        $this->client = new CircleRestClient($this->curl);
    }

    private function join($path, string $query = "") : string
    {
        /**
         * Joins uri with path
         */
        $url = $this->uri;

        if ($path[0] === '/'){
            $url = sprintf("%s%s", $url, $path);
        }
        else{
            $url = sprintf("%s/%s", $url, $path);
        }

        if (strlen($query)){
            $url .= '?'. $query;
        }

        return $url;


    }

    /**
     * Makes get request
     * @param $path
     * @param array $options
     * @return \Circle\RestClientBundle\Services\Response|\Symfony\Component\HttpFoundation\Response|void
     */
    public function get($path, array $body, array $options = array()) : Response
    {
        $query = http_build_query($body);
        $response = $this->client->get($this->join($path, $query), $options);


        return $response;
    }

    /**
     * Makes post request
     * @param $path
     * @param array $body
     * @param array $options
     * @return Response
     */
    public function post($path, array $body, array $options = array()) : Response
    {
        $response = $this->client->post($this->join($path), json_encode($body), $options);

        return $response;
    }

    /**
     * Makes put request
     * @param $path
     * @param array $body
     * @param array $options
     * @return Response
     */
    public function put($path, array $body, array $options = array()) : Response
    {
        $response = $this->client->put($this->join($path), json_encode($body), $options);
        return $response;
    }

    /**
     * Makres delete request
     * @param $path
     * @param array $body
     * @param array $options
     * @return Response
     */
    public function delete($path, array $body, array $options = array()) : Response
    {
        $query = http_build_query($body);
        $response = $this->client->delete($this->join($path, $query), $options);
        return $response;
    }

    /**
     * Makes an query to platform service.
     * @param string $routeName
     * @param array $parameters
     * @return \XA\PlatformClient\Rest\QueryResult
     * @throws RestException
     */
    public function query(string $routeName, array $parameters = array()) : QueryResult
    {
        /**
         * Gets route's parameters
         */
        $route = RouteDefinitions::getRoute(str_replace('@', '', $routeName));

        /**
         * If $route is null, it means that route is not defined.
         */
        if (empty($route)){
            throw RestException::routeNotFound($routeName);
        }

        /**
         * Route definition must have declared method.
         * If method is not specified, throws an exception
         */
        if (!isset($route['method'])){
            throw RestException::routeMethodNotDefined($routeName);
        }

        /**
         * Route definition must have declared path.
         * If path is not specified, throws an exception.
         */
        if (!isset($route['path'])){
            throw RestException::routePathNotDefined($routeName);
        }


        if (isset($route['auth'])){
            if ($route['auth'] === true){
                $this->sendAuthHeaders();
            }
        }

        $route['method'] = strtolower($route['method']);


        $parameters = $this->translateParameterAliases($parameters);

        $response = null;

        $this->initialize();

        /**
         * On GET method
         */
        if ($route['method'] === 'get'){
            $response = $this->get($route['path'], $parameters);
        }
        /**
         * On POST method
         */
        else if ($route['method'] === 'post'){
            $response = $this->post($route['path'], $parameters);
        }
        /**
         * On PUT method
         */
        else if ($route['method'] === 'put'){
            $response = $this->put($route['path'], $parameters);
        }
        /**
         * On DELETE method
         */
        else if ($route['method'] === 'delete'){
            $response = $this->delete($route['path'], $parameters);
        }
        /**
         * On supported HTTP method
         */
        else{
            throw RestException::methodUnsupported($route['method']);
        }


        $queryResult = new QueryResult();
        $queryResult->setQueryResult($response->getContent());
        $queryResult->setQueryStatusCode($response->getStatusCode());

        return $queryResult;
    }

    /**
     * Translates array keys, where keys are alias to standarized parameter's name
     * @param array $parameters
     * @return array
     * @throws RequestParameterException
     */
    protected function translateParameterAliases(array $parameters) : array
    {
        $translatedArray = array();

        foreach($parameters as $parameterAlias => $parameterValue){
            if ($parameterAlias[0] === '@'){
                $aliasName = str_replace('@', '', $parameterAlias);
                $parameterName = RequestDefinitions::getRequestParam($aliasName);

                /**
                 * If source parameter's name was not found,
                 * throws an exception
                 */
                if (empty($parameterName)){
                    throw RequestParameterException::parameterNotFoundByAlias($aliasName);
                }

                $translatedArray[$parameterName] = $parameterValue;
            }
            /**
             * If parameter is not reference to standarized parameter name (is not alias)
             */
            else{
                $translatedArray[$parameterAlias] = $parameterValue;
            }
        }

        return $translatedArray;
    }

    /**
     * Translates path from path's alias to path's source
     * @param string $pathAlias
     * @return string
     */
    protected function translatePath(string $pathAlias) : string
    {
        /**
         * Verifies if given path is an path's alias
         */
        if ($pathAlias[0] === '@'){
            $aliasName = str_replace('@', '', $pathAlias);

            return RouteDefinitions::getRoute($aliasName);
        }

        return $pathAlias;
    }

    /**
     * Sets headers to auth critical action
     */
    protected function sendAuthHeaders()
    {
        $sessionId = GlobalRequest::getSessionIdFromCookie();
        $sessionHandleKey = GlobalRequest::getSessionHandleKeyFromCookie();

        $this->setHeader('xa-modifier-sid', !empty($sessionId) ? $sessionId : "null");
        $this->setHeader('xa-modifier-shk', !empty($sessionHandleKey) ? $sessionHandleKey : "null");
    }


}

?>
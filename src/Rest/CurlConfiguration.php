<?php

namespace XA\PlatformClient\Rest;

class CurlConfiguration
{
    /**
     * Curl configuration
     * @var array
     */
    private $curlConfig = [
        "defaults" => [
            CURLOPT_HTTPHEADER => [ 'Content-Type: application/json' ],
            CURLOPT_MAXREDIRS => 25,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT=> 25,
            CURLOPT_CRLF => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true
        ]

    ];

    public function __construct()
    {

    }

    /**
     * Sets application key (sets header AX-PLATFORM-APP)
     * @param string $appKey
     */
    public function setAppKey (string $appKey)
    {
        $arr = & $this->curlConfig['defaults'][CURLOPT_HTTPHEADER];
        array_push($arr, sprintf('AX-PLATFORM-APP: %s', $appKey));
    }


    /**
     * Sets HTTP header
     * @param string $headerName
     * @param string $value
     */
    public function setHTTPHeader(string $headerName, string $value)
    {
        $arr = & $this->curlConfig['defaults'][CURLOPT_HTTPHEADER];
        array_push($arr, sprintf("%s: %s", $headerName, $value));

    }

    /**
     * Gets curl configuration as array
     * @return array
     */
    public function getCurlConfiguration() : array
    {
        return ["curl" => $this->curlConfig];
    }

    /**
     * Gets curl defaults configuration
     * @return array
     */
    public function getCurlDefaults() : array
    {
        return $this->curlConfig['defaults'];
    }

    /**
     * Gets HTTP headers
     * @return array
     */
    public function getHTTPHeaders() : array
    {
        return $this->curlConfig['defaults'][CURLOPT_HTTPHEADER];
    }
}

?>
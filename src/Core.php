<?php

namespace XA\PlatformClient;

use XA\PlatformClient\Auth\PlatformCredentials;
use XA\PlatformClient\Cache\CacheDriverParameters;
use XA\PlatformClient\Dist\Scope;
use XA\PlatformClient\Rest\RestClient;
use XA\PlatformClient\Cache\CacheDriver;

class Core
{
    /**
     * Rest client
     * @var \XA\PlatformClient\Rest\RestClient
     */
    private static $restClient = null;

    /**
     * Platform credentials
     * @var \XA\PlatformClient\Auth\PlatformCredentials
     */
    private $platformCredentials = null;

    /**
     * @var \XA\PlatformClient\Cache\CacheDriver
     */
    private static $cacheDriver = null;

    public function __construct()
    {

    }

    /**
     * Sets provider
     * @param PlatformCredentials $provider
     */
    public function setProvider(PlatformCredentials $provider)
    {
        $this->platformCredentials = $provider;
    }

    /**
     * Sets cache driver parameters. It will also initialize CacheDriver
     * @param CacheDriverParameters $cacheDriverParameters
     */
    public function setCacheParameters(CacheDriverParameters $cacheDriverParameters)
    {
        static::$cacheDriver = new CacheDriver($cacheDriverParameters);
    }

    /**
     * Gets cache driver instance
     * @return CacheDriver
     */
    public static function getCache() : CacheDriver
    {
        return static::$cacheDriver;
    }

    /**
     * Gets rest client
     * @return RestClient
     */
    public static function getRest() : RestClient
    {
        return static::$restClient;
    }

    /**
     * Initializes an client
     */
    public function connect()
    {
        static::$restClient = new RestClient($this->platformCredentials);
    }


}


?>
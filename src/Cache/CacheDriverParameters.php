<?php
/**
 * @Author Reverze (hawkmedia24@gmail.com)
 *
 * This module is port of XAPlatformClient.
 *
 * This module allows you to create valid cache driver parameters.
 */

namespace XA\PlatformClient\Cache;

use XA\PlatformClient\Cache\Exception\CacheException;


class CacheDriverParameters
{
    /**
     * Selected cache driver. Default is 'apcu'.
     * @var string
     */
    private $driverType = 'apcu';

    /**
     * Optional parameters for selected cache driver.
     * Its required for example: MemcacheDriver.
     * @var array
     */
    private $optionalParameters = [];

    /**
     * Almost everything is cached. Every data has own lifetime * global lifetime multiplier.
     * If you need to extend cache lifetime, increase global lifetime multiplier.
     * Default value is '2';
     * @var int
     */
    private $lifetimeMultiplier = 2;

    public function __construct()
    {

    }

    /**
     * Sets cache driver.
     * You can check supported cache drivers at:
     * http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html
     *
     * @param string $cacheDriver Cache driver name.
     * @throws CacheException
     */
    public function setDriver(string $cacheDriver)
    {
        if (!strlen($cacheDriver)){
            throw CacheException::emptyCacheDriverType();
        }

        /**
         * Checks is given cache driver is supported.
         * If cache driver is not supported by doctrine cache
         * throws an exception
         */
        if (!CacheDriverCompiler::isCacheDriverAvailable($cacheDriver)){
            throw CacheException::cacheDriverIsNotSupported($cacheDriver);
        }

        $this->driverType = strtolower($cacheDriver);
    }

    /**
     * Sets cache's lifetime multiplier.
     * Default value is '2'.
     * @param int $multiplier
     */
    public function setMultiplier(int $multiplier)
    {
        /**
         * Negative value of lifetime multiplier is not allowed.
         */
        $multiplier = abs($multiplier);

        if ($multiplier > 0){
            $this->lifetimeMultiplier = $multiplier;
        }
    }

    public function setParameters(array $parameters = array())
    {
        $this->optionalParameters = $parameters;
    }

    /**
     * Gets cache driver name
     * @return string
     */
    public function getCacheDriverName() : string
    {
        return strtolower($this->driverType);
    }

    /**
     * Gets optional cache driver parameters
     * @return array
     */
    public function getCacheDriverParameters() : array
    {
        return $this->optionalParameters;
    }

    /**
     * Gets lifetime multiplier
     * @return int
     */
    public function getLifetimeMultiplier() : int
    {
        return $this->lifetimeMultiplier;
    }

}


?>
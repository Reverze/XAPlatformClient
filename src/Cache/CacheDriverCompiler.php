<?php

/**
 * @Author Reverze (hawkmedia24@gmail.com)
 *
 * This module is part of XAPlatformClient.
 *
 * This module allows to create suitable cache provider.
 */

namespace XA\PlatformClient\Cache;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\CacheProvider;

class CacheDriverCompiler
{
    /**
     * This array contains all supported cache drivers.
     * Look up http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html
     * @var array
     */
    private static $availableCacheDrivers = [
        "apcu",
        "memcached",
        "xcache",
        "redis"
    ];


    /**
     * Checks if given cache driver is supported.
     * @param string $cacheDriver
     * @return bool
     */
    public static function isCacheDriverAvailable(string $cacheDriver) : bool
    {
        return in_array(strtolower($cacheDriver), static::$availableCacheDrivers);
    }

    /**
     * Compiles cache driver
     * @param CacheDriverParameters $cacheDriverParameters
     * @return CacheProvider
     */
    public static function compileCacheDriver(CacheDriverParameters $cacheDriverParameters) : CacheProvider
    {
        $cacheProvider = null;

        /**
         * Gets optional parameters for cache driver.
         * Some cache driver requires additional parameters.
         */
        $params = $cacheDriverParameters->getCacheDriverParameters();

        /**
         * When 'apcu' driver has been selected.
         * Cache driver 'apcu' requires php's extension 'apcu'.
         * Extension 'apcu' is available for php >= 5.6.
         * 'Apcu' is an replacemnt for outdated 'apc'.
         *
         * On ubuntu-based distributions you can install it like:
         *  apt-get install php7.0-dev
         *  pecl channel-update pecl.php.net
         *  pecl install apcu
         * Don't immediately add 'extension=apcu.so' on php.ini.
         * It should be added automatically on '/etc/php/7.0/cli/conf.d/20-apcu.ini (or equals apache2) etc
         */
        if ($cacheDriverParameters->getCacheDriverName() === 'apcu'){
            $cacheProvider = new ApcuCache();
        }
        if ($cacheDriverParameters->getCacheDriverName() === 'apc'){
            $cacheProvider = new ApcCache();
        }


        /**
         * Parameter 'namespace' is optional.
         * You can define namespace where cache is stored.
         */
        if (array_key_exists('namespace', $params)){
            $cacheProvider->setNamespace($params['namespace']);
        }

        return $cacheProvider;
    }


}

?>
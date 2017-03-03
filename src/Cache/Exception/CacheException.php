<?php

namespace XA\PlatformClient\Cache\Exception;

class CacheException extends \Exception
{
    /**
     * Throws an exception when given cache driver type (name) is empty.
     * @return CacheException
     */
    public static function emptyCacheDriverType() : CacheException
    {
        return (new CacheException("Given cache driver type is empty!"));
    }

    /**
     * Throws an exception when given cache driver is not supported.
     * @param string $cacheDriver
     * @return CacheException
     */
    public static function cacheDriverIsNotSupported(string $cacheDriver) : CacheException
    {
        return (new CacheException(sprintf("Given cache driver '%s' is not supported! Please refer to: 
            http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html", $cacheDriver)));
    }

    /**
     * Throws an exception when compile cache driver failed
     * @param string $cacheDriver
     * @return CacheException
     */
    public static function compileCacheDriverFailed(string $cacheDriver) : CacheException
    {
        return (new CacheException(sprintf("Unexpected error occurred while compile cache driver '%s'", $cacheDriver)));
    }

    /**
     * Throws an exception when given resourceId is empty.
     * @return CacheException
     */
    public static function emptyResourceId() : CacheException
    {
        return (new CacheException("Given resourceId is empty."));
    }

}

?>
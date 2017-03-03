<?php

namespace XA\PlatformClient\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use XA\PlatformClient\Cache\Exception\CacheException;


class CacheDriver
{
    /**
     * Doctrine cache provider
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cacheProvider = null;

    /**
     * Cache driver parameters
     * @var \XA\PlatformClient\Cache\CacheDriverParameters
     */
    private $cacheDriverParameters = null;


    public function __construct(CacheDriverParameters $cacheDriverParameters)
    {
        $this->initializeCacheProvider($cacheDriverParameters);
        $this->cacheDriverParameters = $cacheDriverParameters;
    }

    /**
     * Initializes cache provider
     * @param CacheDriverParameters $cacheDriverParameters
     * @throws CacheException
     */
    protected function initializeCacheProvider(CacheDriverParameters $cacheDriverParameters)
    {
        /**
         * Creates suitable cache provider
         */
        $cacheProvider = CacheDriverCompiler::compileCacheDriver($cacheDriverParameters);

        if (empty($cacheProvider)){
            throw CacheException::compileCacheDriverFailed($cacheDriverParameters->getCacheDriverName());
        }

        $this->cacheProvider = $cacheProvider;
    }

    /**
     * Stores data into cache
     * @param string $resourceId
     * @param $data
     * @param int $lifetime Cached entry lifetime in seconds. If 0 => infinite
     * @return bool True if entry was successfully stored, False otherwise.
     * @throws CacheException
     */
    public function save(string $resourceId, $data, int $lifetime = 0)
    {
        /**
         * Lifetime cannot be negative value.
         * Instead of throwing exception, gets absolute value.
         */
        $lifetime = abs($lifetime);

        /**
         * If non-infinite lifetime was specified, multiplies by multiplier.
         */
        if ($lifetime > 0){
            $lifetime = $this->cacheDriverParameters->getLifetimeMultiplier() * $lifetime;
        }



        /**
         * 'ResourceId' cannot be empty string.
         * If 'resourceId' is empty throws an exception.
         */
        if (!strlen($resourceId)){
            throw CacheException::emptyResourceId();
        }

        return $this->cacheProvider->save($resourceId, $data, $lifetime);
    }

    /**
     * Checks if entry is exists in cache
     * @param string $resourceId
     * @return bool
     * @throws CacheException
     */
    public function has(string $resourceId) : bool
    {
        /**
         * 'ResourceId' cannot be empty string.
         * If 'resourceId' is empty throws an exception.
         */
        if (!strlen($resourceId)){
            throw CacheException::emptyResourceId();
        }

        return $this->cacheProvider->contains($resourceId);
    }

    /**
     * Gets entry value from cache.
     * @param string $resourceId
     * @throws CacheException
     * @return false|mixed
     */
    public function get(string $resourceId)
    {
        /**
         * 'ResourceId' cannot be empty string.
         * If 'resourceId' is empty throws an exception.
         */
        if (!strlen($resourceId)){
            throw CacheException::emptyResourceId();
        }

        return $this->cacheProvider->fetch($resourceId);
    }

    /**
     * Removes entry from cache
     * @param string $resourceId
     * @return bool
     * @throws CacheException
     */
    public function delete(string $resourceId)
    {
        /**
         * 'ResourceId' cannot be empty string.
         * If 'resourceId' is empty throws an exception.
         */
        if (!strlen($resourceId)){
            throw CacheException::emptyResourceId();
        }

        return $this->cacheProvider->delete($resourceId);
    }



}

?>
<?php

namespace XA\PlatformClient\Dist;

use XA\PlatformClient\Core;
use XA\PlatformClient\Dist\Exception\ScopeException;
use XA\PlatformClient\Rest\QueryResult;


class Scope
{
    /**
     * Scope instance name
     * @var string
     */
    private $scopeName = null;

    /**
     * Cache resource lifetime (in seconds)
     * @var int
     */
    private $cacheResourceLifetime = 0;

    /**
     * @var \XA\PlatformClient\Cache\CacheDriver
     */
    private $cacheDriver = null;

    /**
     * @var \XA\PlatformClient\Rest\RestClient
     */
    private $restClient = null;

    /**
     * Query result
     * @var \XA\PlatformClient\Rest\QueryResult;
     */
    private $queryResult = null;

    /**
     * Compiled scope's name (parameter's value inserted)
     * @var string|null
     */
    private $compiledScopeName = null;

    /**
     * Determines if waiting to manually store is enabled.
     * @var bool
     */
    private $waitForStore = false;

    public function __construct($scopeName, int $cacheLifetime = 0)
    {
        /**
         * Scope's name cannot be empty
         */
        if (is_string($scopeName)){
            if (!strlen($scopeName)){
                throw ScopeException::emptyScopeName();
            }
        }

        $this->scopeName = $scopeName;

        $this->cacheResourceLifetime = abs($cacheLifetime);

        $this->cacheDriver= Core::getCache();
        $this->restClient = Core::getRest();
    }


    public function on(string $path, array $parameters = array())
    {
        if (is_string($this->scopeName)) {
            /**
             * Compiles cache resourceId based on scope's name and given parameters
             */
            $resourceId = $this->compileCacheResourceId($parameters);

            /**
             * Stores compiled scope's name for future scope's use
             */
            $this->compiledScopeName = $resourceId;


            /**
             * If resource is cached, creates query result from cache
             */
            if ($this->cacheDriver->has($resourceId)) {
                $queryResult = new QueryResult();
                $queryResult->setQueryResult($this->cacheDriver->get($resourceId));
                $queryResult->setQueryStatusCode(QueryResult::QUERY_OK);
                $this->queryResult = $queryResult;
                return $this;
            }
        }


        $queryResult = $this->restClient->query($path, $parameters);

        /**
         * Stores query result in scope
         */
        $this->queryResult = $queryResult;

        /**
         * If query result is ok, stores an response in the cache
         */
        if ($this->queryResult->isOk() && is_string($this->scopeName) && !$this->waitForStore){
            $this->cacheDriver->save($resourceId, $this->queryResult->getResult(), $this->cacheResourceLifetime);
        }

        return $this;
    }

    /**
     * Compiles cache resourceId based on scope's name and given parameters
     * @param string $parameters
     * @return mixed|string
     */
    private function compileCacheResourceId(array $parameters)
    {
        $compiled = $this->scopeName;
        /**
         * Regex pattern to match defined parameters in scope's name
         */
        $pattern = '/\{[a-zA-Z\_\-\.]+\}/';

        /**
         * Defined references to parameters in scope's name
         */
        $matchedParameters = array();

        preg_match_all($pattern, $compiled, $matchedParameters);

        $matchedParameters = $matchedParameters[1] ?? array();

        foreach($parameters as $parameterName => $parameterValue){
            /**
             * If parameter name is an parameter alias.
             * Reference to parameter by alias is preceded by '@'
             */
            if ($parameterName[0] === '@'){
                $parameterName = str_replace("@", "", $parameterName);
            }
            $compiled = str_replace('{' . $parameterName . '}', $parameterValue, $compiled);
        }

        return $compiled;
    }

    /**
     * Compiles scope's name on demand
     * @param array $parameters
     * @throws ScopeException
     */
    public function compileName(array $parameters = array())
    {
        if (!empty($this->compiledScopeName)){
            throw ScopeException::recompileScopeName();
        }

        $this->compiledScopeName = $this->compileCacheResourceId($parameters);
    }


    /**
     * Enables waiting to manually store
     */
    public function wait()
    {
        $this->waitForStore = true;
    }

    /**
     * Checks if query result is OK
     * @return array
     */
    public function isOk()
    {
        return $this->queryResult->isOk();
    }

    /**
     * Gets query result
     * @return array
     */
    public function getResult()
    {
        return $this->queryResult->getResult();
    }

    /**
     * Sets scope's name.
     * Notice! You cannot override scope's name.
     * @param string $scopeName
     * @throws ScopeException
     */
    public function setScopeName(string $scopeName)
    {
        if (!empty($this->scopeName)){
            throw ScopeException::scopeNameNotDefined();
        }

        if (!strlen($scopeName)){
            throw ScopeException::emptyScopeName();
        }

        $this->scopeName = $scopeName;
    }

    /**
     * Stores scope result
     * @return bool
     * @throws ScopeException
     */
    public function store()
    {
        /**
         * If waiting for manually store is enabled.
         */
        if ($this->waitForStore){
            if (empty($this->compiledScopeName)){
                throw ScopeException::scopeNameNotDefined();
            }

            return $this->cacheDriver->save($this->compiledScopeName, $this->queryResult->getResult(), $this->cacheResourceLifetime);
        }

        if (empty($this->scopeName)){
            throw ScopeException::scopeNameNotDefined();
        }

        return $this->cacheDriver->save($this->scopeName, $this->queryResult->getResult(), $this->cacheResourceLifetime);
    }

    /**
     * Drops scope
     * @return bool
     */
    public function drop()
    {
        if ($this->cacheDriver->has($this->compiledScopeName)){
            return $this->cacheDriver->delete($this->compiledScopeName);
        }
        else{
            return false;
        }
    }


}


?>

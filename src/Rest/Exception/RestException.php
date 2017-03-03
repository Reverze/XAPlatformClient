<?php

namespace XA\PlatformClient\Rest\Exception;

class RestException extends \Exception
{
    /**
     * Throws an exception when route was not found.
     * @param string $routeName
     * @return RestException
     */
    public static function routeNotFound(string $routeName) : RestException
    {
        return (new RestException(sprintf("Route '%s' was not found!", $routeName)));
    }

    /**
     * Throws an exception when parameter 'method' is not defined.
     * @param string $routeName
     * @return RestException
     */
    public static function routeMethodNotDefined(string $routeName) : RestException
    {
        return (new RestException(sprintf("Missed parameter 'method' in definition of route '%s'", $routeName)));
    }

    /**
     * Throws an exception when parameter 'path' is not defined.
     * @param string $routeName
     * @return RestException
     */
    public static function routePathNotDefined(string $routeName) : RestException
    {
        return (new RestException(sprintf("Missed parameter 'path' in definition of route '%s'", $routeName)));
    }

    /**
     * Throws an exception when unsupported HTTP method was given.
     * @param string $method
     * @return RestException
     */
    public static function methodUnsupported(string $method) : RestException
    {
        return (new RestException(sprintf("HTTP method '%s' is unsupported!", $method)));
    }

}

?>
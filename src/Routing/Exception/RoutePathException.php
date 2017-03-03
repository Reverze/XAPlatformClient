<?php

namespace XA\PlatformClient\Routing\Exception;

class RoutePathException extends \Exception
{
    /**
     * Throws an exception when route's path was not found by path's alias.
     * @param string $pathAlias
     * @return RoutePathException
     */
    public static function routePathNotFoundByAlias(string $pathAlias) : RoutePathException
    {
        return (new RoutePathException(sprintf("Route's path not found by path's alias '%s'", $pathAlias)));
    }
}

?>
<?php


namespace XA\PlatformClient\Request\Exception;


class RequestParameterException extends  \Exception
{
    /**
     * Throws an exception when standarized request parameter was not found by specified
     * parameter's alias.
     * @param string $parameterAlias
     * @return RequestParameterException
     */
    public static function parameterNotFoundByAlias(string $parameterAlias) : RequestParameterException
    {
        return (new RequestParameterException(sprintf("Request parameter was not found by alias '%s'",$parameterAlias)));
    }
}

?>
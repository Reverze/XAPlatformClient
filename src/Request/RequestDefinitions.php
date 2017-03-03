<?php

namespace XA\PlatformClient\Request;

class RequestDefinitions
{
    /**
     * Standardized request params
     * @var array
     */
    private static $requestParams = [
        "userID" => "user-id",
        "username" => "username",
        "plainPassword" => "plain-password",
        "passwordSalt" => "password-salt",
        "hashedPassword" => "hashed-password",
        "usernameOrEmail" => "mixed",
        "email" => "email",
        "regip" => "registerIPAddress",
        "userGroupName" => "userGroupName",
        "flags" => "flags",
        "recby" => "recomendedBy",
        "confirmed" => "confirmed",
        "sestype" => "session-type",
        "uagent" => "user-agent",
        "ipaddr" => "ip-address",
        "sessionID" => "sid",
        "sessionHandleKey" => "shk",
        "confirmCode" => "code",
        "authCode" => "authorize-code"
    ];

    /**
     * Gets request parameter by parameter's alias
     * @param string $paramAlias
     * @return string|null
     */
    public static function getRequestParam(string $paramAlias)
    {
        if (isset(static::$requestParams[$paramAlias])){
            return static::$requestParams[$paramAlias];
        }
        else{
            return null;
        }
    }

    /**
     * Gets all request parameters and aliases
     * @return array
     */
    public static function getRequestParameters() : array
    {
        return static::$requestParams;
    }

}


?>
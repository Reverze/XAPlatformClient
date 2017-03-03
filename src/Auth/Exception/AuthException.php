<?php

namespace XA\PlatformClient\Auth\Exception;

class AuthException extends \Exception
{
    /**
     * Throws an exception when given provider's hostname is empty.
     * @return AuthException
     */
    public static function emptyProvider() : AuthException
    {
        return (new AuthException("Provider's hostname cannot be empty!"));
    }

    /**
     * Throws an exception when given provider's port does not fall within the scope.
     * (0 - 65535)
     * @return AuthException
     */
    public static function invalidPortRange() : AuthException
    {
        return (new AuthException("Invalid port. Allowed port range is from 0 to 65535"));
    }

    /**
     * Throws an exception when given app key identifier is empty.
     * @return AuthException
     */
    public static function emptyAppKey() : AuthException
    {
        return (new AuthException("Given app key identifier is empty!"));
    }
}

?>
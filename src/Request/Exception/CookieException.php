<?php

namespace XA\PlatformClient\Request\Exception;

class CookieException extends \Exception
{
    /**
     * Throws an exception when cookie's name is empty
     * @return CookieException
     */
    public static function emptySessionIdCookieName() : CookieException
    {
        return (new CookieException("Empty cookie's name for cookie which stores sessionId"));
    }

    /**
     * Throws an exception when cookie's name is empty
     * @return CookieException
     */
    public static function emptySessionHandleKeyCookieName() : CookieException
    {
        return (new CookieException("Empty cookie's name for cookie which stores sessionHandleKey"));
    }
}

?>
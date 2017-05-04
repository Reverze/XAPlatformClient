<?php
/**
 * @Author Reverze (hawkmedia24@gmail.com)
 *
 * This package is a part of XAPlatformClient
 */

namespace XA\PlatformClient\Request;

use XA\PlatformClient\Request\Exception\CookieException;
use Josantonius\Ip\Ip;

class GlobalRequest
{
    /**
     * Cookie's name.
     * This cookie stores sessionId
     * @var string
     */
    private static $sessionIdCookieName = "xa-platform-client-ses-id";

    /**
     * Cookie's name.
     * This cookie stores sessionHandleKey
     * @var string
     */
    private static $sessionHandleKeyCookieName = "xa-platform-client-ses-hk";

    /**
     * Cookie's lifetime in seconds
     * @var int
     */
    private static $cookieLifetime = 7200;

    /**
     * Sets custom cookie's name for cookie which stores sessionId
     * @param string $cookieName
     * @throws CookieException
     */
    public static function setSessionIdCookieName(string $cookieName)
    {
        /**
         * Cookie's name cannot be empty.
         */
        if (!strlen($cookieName)){
            throw CookieException::emptySessionIdCookieName();
        }

        static::$sessionIdCookieName = $cookieName;
    }

    /**
     * Sets custom cookie's name for cookie which stores sessionHandleKey
     * @param string $cookieName
     * @throws CookieException
     */
    public static function setSessionHandleKeyCookieName(string $cookieName)
    {
        /**
         * Cookie's name cannot be empty.
         */
        if (!strlen($cookieName)){
            throw CookieException::emptySessionHandleKeyCookieName();
        }

        static::$setSessionHandleKeyCookieName = $cookieName;
    }

    /**
     * Reads cookie which stores sessionId.
     * If cookie is not defined returns null.
     * @return string|null
     */
    public static function getSessionIdFromCookie()
    {
        /**
         * Not always cookie is defined at _COOKIE array
         */
        if (isset($_COOKIE[static::$sessionIdCookieName])){
            return $_COOKIE[static::$sessionIdCookieName];
        }
        else{
            return null;
        }
    }

    /**
     * Reads cookie which stores sessionHandleKey.
     * If cookie is not defined returns null.
     * @return string|null
     */
    public static function getSessionHandleKeyFromCookie()
    {
        if (isset($_COOKIE[static::$sessionHandleKeyCookieName])){
            return $_COOKIE[static::$sessionHandleKeyCookieName];
        }
        else{
            return null;
        }
    }

    /**
     * Gets user agent.
     * If cannot recognize user agent, returns null
     * @return string|null
     */
    public static function getUserAgent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])){
            return $_SERVER['HTTP_USER_AGENT'];
        }
        else{
            return null;
        }
    }

    /**
     * Gets user ip address.
     * If cannot recognize user agent, returns null
     * @return false|null|string
     */
    public static function getUserIpAddress()
    {
        return $_SERVER['REMOTE_ADDR'] ?? "";
        //$ip = IP::get();

        //return ($ip !== false ? $ip : null);
    }

    /**
     * Sets cookie
     * @param string $cookieValue
     */
    public static function setSessionIdCookie(string $cookieValue)
    {
        setcookie(static::$sessionIdCookieName, $cookieValue,  time() + static::$cookieLifetime, '/',  $_SERVER['HTTP_HOST'] ?? "");
        $_COOKIE[static::$sessionIdCookieName] = $cookieValue;
    }

    /**
     * Sets cookie
     * @param string $cookieValue
     */
    public static function setSessionHandleKeyCookie(string $cookieValue)
    {
        setcookie(static::$sessionHandleKeyCookieName, $cookieValue, time() + static::$cookieLifetime, '/', $_SERVER['HTTP_HOST'] ?? "");
        $_COOKIE[static::$sessionHandleKeyCookieName] = $cookieValue;
    }

    /**
     * Drops cookie
     */
    public static function dropSessionIdCookie()
    {
        setcookie(static::$sessionIdCookieName, '', 99600, '/', $_SERVER['HTTP_HOST'] ?? "");
        if (isset($_COOKIE[static::$sessionIdCookieName])){
            unset($_COOKIE[static::$sessionIdCookieName]);
        }
    }

    /**
     * Drops cookie
     */
    public static function dropSessionHandleKeyCookie()
    {
        setcookie(static::$sessionHandleKeyCookieName, '', 99600, '/', $_SERVER['HTTP_HOST'] ?? "");
        if (isset($_COOKIE[static::$sessionHandleKeyCookieName])){
            unset($_COOKIE[static::$sessionHandleKeyCookieName]);
        }
    }

}

?>
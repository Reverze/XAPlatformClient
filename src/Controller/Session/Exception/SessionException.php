<?php

namespace XA\PlatformClient\Controller\Session\Exception;

class SessionException extends \Exception
{
    /**
     * Throws an exception while unexpected error occurred while fetching details about session instance
     * @param string $sid
     * @param string $shk
     * @return SessionException
     */
    public static function fetchSessionException(string $sid, string $shk) : SessionException
    {
        return (new SessionException(sprintf("Unexpected error occurred while fetching details about
            session (sid: '%s'; shk: '%s'", $sid, $shk)));
    }

    /**
     * Throws an exception if session's owner was not found by userID.
     * @param int $userId
     * @return SessionException
     */
    public static function userNotFound(int $userId) : SessionException
    {
        return (new SessionException(sprintf("User was not found by userId '%d'", $userId)));
    }

    /**
     * Throws an exception when unexpected error occurred while drop single session instance
     * @param string $sid
     * @return SessionException
     */
    public static function unexpectedErrorOccurredWhileDropSession(string $sid) : SessionException
    {
        return (new SessionException(spritnf("Unexpected error occurred while drop single session instance '%s'", $sid)));
    }

}


?>
<?php

namespace XA\PlatformClient\Controller\User\Exception;

use HBMasterBundle\Service\Restrict\User;

class UserException extends \Exception
{
    /**
     * Throws an exception when provider forgot to returns session instance identifiers.
     * @param string $usercredentials
     * @return UserException
     */
    public static function providerForgetAboutSession(string $usercredentials) : UserException
    {
        return (new UserException(sprintf("Provider forgot to returns sessionID and sessionHandleKey for
            user who has just signin (user: '%s')", $usercredentials)));
    }

    /**
     * Throws an exception when unexpected error occurred while signin user.
     * @param string $usercredentials
     * @return UserException
     */
    public static function signinUserException(string $usercredentials) : UserException
    {
        return (new UserException(sprintf("Unexpected error occurred while singin user '%s'", $usercredentials)));
    }

    /**
     * Throws an exception when user's details was invalid
     * @param $data
     * @return UserException
     */
    public static function invalidUserData($data) : UserException
    {
        return (new UserException(sprintf("Invalid user data: '%s'", strval($data))));
    }

    /**
     * Throws an exception when user's object was invalid
     * @return UserException
     */
    public static function invalidUserObject() : UserException
    {
        return (new UserException("Invalid user object"));
    }

    /**
     * Throws an exception when given email address has invalid format.
     * @param string $emailAddress
     * @return UserException
     */
    public static function invalidEmailAddress(string $emailAddress) : UserException
    {
        return (new UserException("Given email address has invalid format: '%s'", $emailAddress));
    }

    /**
     * Throws an exception when user is offline on update action
     * @param int $userID
     * @param string $action
     * @return UserException
     */
    public static function updateActionUserOffline(int $userID, string $action) : UserException
    {
        return (new UserException(sprintf("Cannot perform update action on user '%d' cuz user is currently offline", $userID)));
    }

    /**
     * Throws an exception when account is already confirmed
     * @param int $userID
     * @return UserException
     */
    public static function accountAlreadyConfirmed(int $userID) : UserException
    {
        return (new UserException(sprintf("Account (userID: '%d') is already confirmed!")));
    }
}

?>
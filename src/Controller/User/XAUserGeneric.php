<?php

namespace XA\PlatformClient\Controller\User;

use XA\PlatformClient\Dist\Scope;
use XA\PlatformClient\Enum\WebService;

class XAUserGeneric
{
    /**
     * Given username was empty
     */
    const EMPTY_USERNAME = 100;

    /**
     * Given email address was empty
     */
    const EMPTY_EMAIL_ADDRESS = 101;

    /**
     * Given email address was invalid
     */
    const INVALID_EMAIL_ADDRESS = 102;

    /**
     * Given userID was invalid - eg. less or equal zero
     */
    const INVALID_USER_ID = 103;

    /**
     * User's account was not found
     */
    const USER_NOT_FOUND = 104;

    /**
     * Given user's name is reserved
     */
    const RESERVED_USER_NAME = 105;

    /**
     * Given email address is assigned to other user's account
     */
    const RESERVED_EMAIL_ADDRESS = 106;

    /**
     * Given username was invalid
     */
    const INVALID_USER_NAME = 107;

    /**
     * Given userpassword was empty
     */
    const EMPTY_USER_PASSWORD = 108;

    /**
     * Given userpassword was invalid
     */
    const INVALID_USER_PASSWORD = 109;

    /**
     * Given confirm code is empty
     */
    const EMPTY_CONFIRM_CODE = 110;

    /**
     * When user want to confirm account, but user's account is already confirmed.
     */
    const ACCOUNT_ALREADY_CONFIRMED = 111;

    /**
     * Action is not available because user's account is not confirmed
     */
    const ACCOUNT_NOT_CONFIRMED_DENIED = 112;

    /**
     * Given authorize code was empty
     */
    const EMPTY_AUTHORIZE_CODE = 113;

    /**
     * Given authorize code was invalid
     */
    const INVALID_AUTHORIZE_CODE = 114;

    /**
     * Given avatar url was empty
     */
    const EMPTY_AVATAR_URL = 115;

    /**
     * Invalid avatar image mime type
     */
    const AVATAR_INVALID_MIME_TYPE = 116;

    /**
     * Max size of avatar image exceeded
     */
    const AVATAR_MAX_SIZE_EXCEEDED = 117;

    /**
     * Unexpected error occurred (scope is not ok)
     */
    const UNEXPECTED_ERROR = 500;

    public function __construct()
    {

    }

    /**
     * Checks if username is available
     * @param string $username
     * @return bool|int
     */
    public function isUserNameAvailable(string $username)
    {
        if (!strlen($username)){
            return self::EMPTY_USERNAME;
        }

        $scope = new Scope('is_user_name_available_{username}', 50);
        $scope->wait();
        $scope->on('@users.isUsernameExists', [
            '@username' => $username
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if ($scopeResult['result'] === true){
                $scope->store();
            }

            return (bool) !$scopeResult['result'];
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Checks if email address is available. If email address is available it means that email address
     * is not assigned to any user.
     * @param string $emailAddress
     * @return bool|int
     */
    public function isEmailAddressAvailable(string $emailAddress)
    {
        if (!strlen($emailAddress)){
            return self::EMPTY_EMAIL_ADDRESS;
        }

        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)){
            return self::INVALID_EMAIL_ADDRESS;
        }

        $scope = new Scope('us_email_address_available_{email}', 200);
        $scope->wait();
        $scope->on('@users.isEmailAddressExists', [
            '@email' => $emailAddress
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if ($scopeResult['result'] === true){
                $scope->store();
            }

            return (bool) !$scopeResult['result'];
        }

        return self::UNEXPECTED_ERROR;
    }


    /**
     * Checks if user exists by userID
     * @param int $userID
     * @return bool|int
     */
    public function isUserExistsByUserId(int $userID)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        $scope = new Scope('is_user_exists_{userID}', 200);
        $scope->wait();
        $scope->on('@users.isUserExistsByUserId', [
            '@userID' => (int) $userID
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if ($scopeResult['result'] === true){
                $scope->store();
            }

            return (bool) !$scopeResult['result'];
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Gets user's object by userID
     * @param int $userID
     * @return int|XAUserVirtual
     */
    public function getUserObjectByUserId(int $userID)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        /**
         * We enable scope in waiting mode. Why?
         * We only want to store user object only when user was found.
         * If user was not found, we do not store this fact.
         */
        $scope = new Scope('foreign_user_object_{userID}', 200);
        $scope->wait();
        $scope->on('@users.getUserObjectByUserId', [
            '@userID' => (int) $userID
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'user-not-found'){
                    return self::USER_NOT_FOUND;
                }
            }
            else{
                $scope->store();
            }

            return new XAUserVirtual($scopeResult);
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Gets user's object by username
     * @param string $userName
     * @return int|XAUserVirtual
     */
    public function getUserObjectByUserName(string $userName)
    {
        if (!strlen($userName)){
            return self::EMPTY_USERNAME;
        }

        $scope = new Scope('foreign_user_object_by_name_{username}', 200);
        $scope->wait();
        $scope->on('@users.getUserObjectByUserName', [
            '@username' => (string) $userName
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'user-not-found'){
                    return self::USER_NOT_FOUND;
                }
            }
            else{
                $scope->store();
            }

            return new XAUserVirtual($scopeResult);
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Gets user's object by assigned email address
     * @param string $emailAddress
     * @return int|XAUserVirtual
     */
    public function getUserObjectByEmailAddress(string $emailAddress)
    {
        if (!strlen($emailAddress)){
            return self::EMPTY_EMAIL_ADDRESS;
        }

        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)){
            return self::INVALID_EMAIL_ADDRESS;
        }

        $scope = new Scope('foreign_user_object_by_email_{email}', 200);
        $scope->wait();
        $scope->on('@users.getUserObjectByEmailAddress', [
            '@email' => $emailAddress
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'user-not-found'){
                    return self::USER_NOT_FOUND;
                }
            }
            else{
                $scope->store();
            }

            return new XAUserVirtual($scopeResult);
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Updates username
     * @param int $userID
     * @param string $newUserName
     * @return bool|int
     */
    public function updateUserName(int $userID, string $newUserName)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($newUserName)){
            return self::EMPTY_USERNAME;
        }

        $scope = new Scope(null, 200);
        $scope->on('@users.updateUserName', [
            '@userID' => $userID,
            '@username' => $newUserName
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'username-not-available'){
                    return self::RESERVED_USER_NAME;
                }
                if ($scopeResult['result'] === 'username-invalid'){
                    return self::INVALID_USER_NAME;
                }

                return (bool) $scopeResult['result'];

            }
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Updates user's email
     * @param int $userID
     * @param string $newEmailAddress
     * @return bool|int
     */
    public function updateUserEmailAddress(int $userID, string $newEmailAddress)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($newEmailAddress)){
            return self::EMPTY_EMAIL_ADDRESS;
        }

        if (!filter_var($newEmailAddress, FILTER_VALIDATE_EMAIL)){
            return self::INVALID_EMAIL_ADDRESS;
        }

        $scope = new Scope(null, 200);
        $scope->on('@users.updateEmailAddress', [
            '@userID' => $userID,
            '@email' => $newEmailAddress
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'email-not-available'){
                    return self::RESERVED_EMAIL_ADDRESS;
                }
                if ($scopeResult['result'] === 'invalid-email-address'){
                    return self::INVALID_EMAIL_ADDRESS;
                }

                return (bool) $scopeResult['result'];
            }

        }

        return self::UNEXPECTED_ERROR;
    }



    /**
     * Confirms user's account
     * @param int $userID
     * @param string $confirmCode
     * @return bool|int
     */
    public function confirmUserAccount(int $userID, string $confirmCode)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($confirmCode)){
            return self::EMPTY_CONFIRM_CODE;
        }

        $scope = new Scope(null);
        $scope->on('@users.confirmAccount', [
            '@userID' => $userID,
            '@confirmCode' => $confirmCode
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'account-already-confirmed'){
                    return self::ACCOUNT_ALREADY_CONFIRMED;
                }

                if ($scopeResult['result'] === 'account-confirm-failed'){
                    return false;
                }

                if ($scopeResult['result'] === 'account-confirm-success'){
                    return true;
                }
            }

        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * First stage of password change.
     * User will receive an email with authorize code.
     * This code will be used to finish password change.
     * @param int $userID
     * @param string $password
     * @return bool|int
     */
    public function beginPasswordChange(int $userID, string $password)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($password)){
            return self::EMPTY_USER_PASSWORD;
        }

        $scope = new Scope(null);
        $scope->on('@authcred.beginPasswordChange', [
            '@userID' => $userID,
            '@plainPassword' => $password
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                /**
                 * Given password has invalid password.
                 */
                if($scopeResult['result'] === 'invalid-password'){
                    return self::INVALID_USER_PASSWORD;
                }

                /**
                 * Password change is only available if user's account is confirmed
                 */
                if ($scopeResult['result'] === 'account-not-confirmed'){
                    return self::ACCOUNT_NOT_CONFIRMED_DENIED;
                }

                /**
                 * If true -> everything is ok, mail was send
                 * If false -> everything is ok, but mail was not send
                 */
                return (bool) $scopeResult['result'];
            }
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * The last stage of password change.
     * Accepts changes.
     * @param int $userID
     * @param string $authorizeCode
     * @return bool|int
     */
    public function finishPasswordChange(int $userID, string $authorizeCode)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($authorizeCode)){
            return self::EMPTY_AUTHORIZE_CODE;
        }

        $scope = new Scope(null);
        $scope->on('@authcred.finishPasswordChange', [
            '@userID' => $userID,
            '@authCode' => $authorizeCode
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                if ($scopeResult['result'] === 'invalid-authorize-code'){
                    return self::INVALID_AUTHORIZE_CODE;
                }

                if ($scopeResult['result'] === 'user-not-found'){
                    return self::USER_NOT_FOUND;
                }

                return (bool) $scopeResult['result'];

            }
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * First step of change username.
     * Sends email with authorization code
     * @param int $userID
     * @param string $username
     * @return bool|int
     */
    public function beginUsernameChangeProcess(int $userID, string $username)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($username)){
            return self::EMPTY_USERNAME;
        }

        $scope = new Scope(null);
        $scope->on('@users.beginChangeNameProcess', [
            '@userID' => $userID,
            '@username' => $username
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                /**
                 * Username change is only available if user's account is confirmed
                 */
                if ($scopeResult['result'] === 'account-not-confirmed'){
                    return self::ACCOUNT_NOT_CONFIRMED_DENIED;
                }

                /**
                 * Invalid username format
                 */
                if ($scopeResult['result'] === 'username-invalid'){
                    return self::INVALID_USER_NAME;
                }

                return (bool) $scopeResult['result'];
            }
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * The last step of process to change username.
     * @param int $userID
     * @param string $authorizeCode
     * @return bool|int
     */
    public function finishUserNameChangeProcess(int $userID, string $authorizeCode)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($authorizeCode)){
            return self::EMPTY_AUTHORIZE_CODE;
        }

        $scope = new Scope(null);
        $scope->on('@users.finishChangeNameProcess', [
            '@userID' => $userID,
            '@authCode' => $authorizeCode
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                if ($scopeResult['result'] === 'invalid-authorize-code'){
                    return self::INVALID_AUTHORIZE_CODE;
                }

                if ($scopeResult['result'] === 'user-not-found'){
                    return self::USER_NOT_FOUND;
                }

                return (bool) $scopeResult['result'];
            }

        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Do you need docs?
     * @param int $userID
     * @param string $newEmailAddress
     * @return bool|int
     */
    public function createAskChangeEmailAddress(int $userID, string $newEmailAddress)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($newEmailAddress)){
            return self::EMPTY_EMAIL_ADDRESS;
        }

        $scope = new Scope(null);
        $scope->on('@users.askEmailChange', [
            '@userID' => $userID,
            '@email' => $newEmailAddress
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                if ($scopeResult['result'] === 'account-not-confirmed'){
                    return self::ACCOUNT_NOT_CONFIRMED_DENIED;
                }

                if ($scopeResult['result'] === 'invalid-email-address'){
                    return self::INVALID_EMAIL_ADDRESS;
                }

                return (bool) $scopeResult['result'];
            }
        }

        return self::UNEXPECTED_ERROR;
    }


    /**
     * Accepts ask for email address change
     * @param int $userID
     * @param string $authorizeCode
     * @return bool|int
     */
    public function acceptAskChangeEmailAddress(int $userID, string $authorizeCode)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($authorizeCode)){
            return self::EMPTY_AUTHORIZE_CODE;
        }

        $scope = new Scope(null);
        $scope->on('@users.acceptAskEmailChange', [
            '@userID' => $userID,
            '@authCode' => $authorizeCode
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                if ($scopeResult['result'] === 'account-not-confirmed'){
                    return self::ACCOUNT_NOT_CONFIRMED_DENIED;
                }

                if ($scopeResult['result'] === 'invalid-authorize-code'){
                    return self::INVALID_AUTHORIZE_CODE;
                }

                if ($scopeResult['result'] === 'user-not-found'){
                    return self::USER_NOT_FOUND;
                }

                return (bool) $scopeResult['result'];
            }
        }

        return self::UNEXPECTED_ERROR;
    }


    /**
     * Finishes email address change
     * @param int $userID
     * @param string $authorizeCode
     * @return bool|int
     */
    public function finishChangeEmailAddress(int $userID, string $authorizeCode)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($authorizeCode)){
            return self::EMPTY_AUTHORIZE_CODE;
        }

        $scope = new Scope(null);
        $scope->on('@users.finishEmailChange', [
            '@userID' => $userID,
            '@authCode' => $authorizeCode
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                if ($scopeResult['result'] === 'account-not-confirmed'){
                    return self::ACCOUNT_NOT_CONFIRMED_DENIED;
                }

                if ($scopeResult['result'] === 'invalid-authorize-code'){
                    return self::INVALID_AUTHORIZE_CODE;
                }

                if ($scopeResult['result'] === 'user-not-found'){
                    return self::USER_NOT_FOUND;
                }

                return (bool) $scopeResult['result'];
            }
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Chages user avatar. Returns True on success
     * @param int $userID
     * @param string $newAvatarUrl
     * @return bool|int
     */
    public function changeUserAvatarUrl(int $userID, string $newAvatarUrl)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        if (!strlen($newAvatarUrl)){
            return self::EMPTY_AVATAR_URL;
        }

        $scope = new Scope();
        $scope->on('@users.updateUserAvatarUri', [
            '@userID' => $userID,
            '@avatar' => $newAvatarUrl
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                if ($scopeResult['result'] === 'account-not-confirmed'){
                    return self::ACCOUNT_NOT_CONFIRMED_DENIED;
                }

                if ($scopeResult['result'] === 'external-webservice-not-available'){
                    return WebService::NOT_AVAILABLE;
                }

                if ($scopeResult['result'] === 'external-resource-not-found'){
                    return WebService::RESOURCE_NOT_FOUND;
                }

                if ($scopeResult['result'] === 'external-resource-not-available'){
                    return WebService::RESOURCE_NOT_AVAILABLE;
                }

                return (bool) $scopeResult['result']; //true
            }
        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Drops user avatar
     * @param int $userID
     * @return bool|int
     */
    public function dropUserAvatar(int $userID)
    {
        if ($userID <= 0){
            return self::INVALID_USER_ID;
        }

        $scope = new Scope();
        $scope->on('@users.dropUserAvatar', [
            '@userID' => $userID
        ]);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                return (bool) $scopeResult['result'];
            }

        }

        return self::UNEXPECTED_ERROR;
    }
}

?>
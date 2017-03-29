<?php

namespace XA\PlatformClient\Controller\User;

use HBMasterBundle\Service\Restrict\User;
use Symfony\Component\Finder\Expression\Glob;
use XA\PlatformClient\Controller\Session\XASession;
use XA\PlatformClient\Controller\User\Exception\UserException;
use XA\PlatformClient\Dist\Scope;
use XA\PlatformClient\Request\GlobalRequest;

class XAUser
{
    /**
     * User typed invalid password
     */
    const INVALID_PASSWORD = 124;

    /**
     * User account was not found
     */
    const USER_NOT_FOUND = 125;

    /**
     * Given email address had invalid format
     */
    const INVALID_EMAIL_ADDRESS = 130;

    /**
     * Given username was empty
     */
    const EMPTY_USERNAME = 131;

    /**
     * Given user's password was empty
     */
    const EMPTY_USERPASSWORD = 132;

    /**
     * Given new username is the same like previous
     */
    const THE_SAME_NAME = 124;

    /**
     * Given new email address was the same like previous
     */
    const THE_SAME_EMAIL = 125;

    /**
     * Unexpected error occurred
     */
    const UNEXPECTED_ERROR = 126;

    /**
     * Given username was invalid
     */
    const INVALID_USER_NAME = 127;
    /**
     * Everything is ok
     */
    const OK = 200;

    /**
     * Given username is reserved by other user
     */
    const RESERVED_USER_NAME = 128;

    /**
     * Given email address is reserved by other user
     */
    const RESERVED_EMAIL_ADDRESS = 129;

    const PREPARE_CONFIRMATION_FAILED = 130;

    /**
     * Given avatar url was empty
     */
    const EMPTY_AVATAR_URL = 131;


    const MAIL_SEND_FAILED = 131;
    /**
     * Unique userID.
     * Each user's account is identified by userID.
     * @var int
     */
    protected $userID = 0;

    /**
     * Unique user email address.
     * Only one email address can be assigned to user account.
     * It is also possible to identify user account by email address (but it is no recommened
     * due ability to change email address by user)
     * @var string|null
     */
    protected $userEmailAddress = null;

    /**
     * Unique user's name.
     * Each user has unique username.
     * It is also possibl to identify user account by username, but it is not recommened
     * due ability to change username by user)
     * @var string|null
     */
    protected $userName = null;

    /**
     * ID of user group to which user is assigned.
     * @var string|null
     */
    protected $currentUserGroupId = null;

    /**
     * Stores timestamp of user registration.
     * @var int
     */
    protected $registerTime = 0;

    /**
     * Stores ip address when user was creating account.
     * @var string|null
     */
    protected $registerIpAddress = null;

    /**
     * Url to user avatar image.
     * If user not has specified custom user avatar, it is set as null.
     * @var string|null
     */
    protected $userAvatarUrl = null;

    /**
     * Current user's flags
     * @var null
     */
    protected $userFlags = null;

    /**
     * Determines is user account is confirmed.
     * @var boolean
     */
    protected $confirmed = false;

    /**
     * Object which represents session instance
     * @var \XA\PlatformClient\Controller\Session\XASession
     */
    private $sessionInstance = null;

    /**
     * Object which represent UserEnvironment
     * @var \XA\PlatformClient\Controller\User\XAUserEnvironment
     */
    private $environmentInstance = null;

    /**
     * Represent generic methods for managing users
     * @var \XA\PlatformClient\Controller\User\XAUserGeneric
     */
    private $userGenericInstance = null;

    /**
     * Stores user online status.
     * If is null, we need to call method isAlive of XASession to check if session is alive.
     * @var boolean|null
     */
    private $isOnline = null;

    /**
     * Scope's name for module
     * @var string
     */
    private $scopeName = 'user_object_by_userid_{userID}';

    public function __construct(XAUserEnvironment $environment, XAUserGeneric $customGeneric)
    {
        $this->environmentInstance = $environment;

        if (!empty($customGeneric)){
            $this->userGenericInstance = $customGeneric;
        }
        else{
            $this->bootGeneric();
        }

        $this->initializeSessionInstance();
    }

    private function bootGeneric()
    {
        $this->userGenericInstance = new XAUserGeneric();
    }

    /**
     * Initializes session object instance
     */
    private function initializeSessionInstance()
    {
        if (empty($this->sessionInstance)){
            $this->sessionInstance = new XASession();
        }
    }

    /**
     * Checks if user is logged in.
     * @return bool
     */
    public function isOnline() : bool
    {
        if (empty($this->isOnline)){
            $this->isOnline = $this->sessionInstance->isAlive();
        }

        return $this->isOnline;
    }

    /**
     * Signin user
     * @param string $credentials
     * @param string $plainPassword
     * @return int
     * @throws UserException
     */
    public function signin(string $credentials, string $plainPassword) : int
    {
        $userAgent = GlobalRequest::getUserAgent();
        $ipAddress = GlobalRequest::getUserIpAddress();


        /**
         * This scope will be helpful to signin user account.
         * If everything is ok, platform should returns
         * an array with sessionId and sessionHandleKey.
         * After that we create an session object and get
         * more details about session.
         */
        $scope = new Scope(null, 200);
        $scope->on('@users.signin', [
            '@username' => $credentials,
            '@plainPassword' => $plainPassword,
            '@uagent' => $userAgent,
            '@ipaddr' => $ipAddress
        ]);


        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('result', $scopeResult)){
                /** If user typed invalid password */
                if ($scopeResult['result'] === 'invalid-password'){
                    return self::INVALID_PASSWORD;
                }
                /**
                 * If user's account was not found.
                 * It can be caused by typo in username or email address.
                 */
                if ($scopeResult['result'] === 'user-not-found'){
                    return self::USER_NOT_FOUND;
                }

                /**
                 * Error raised by platform
                 */
                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    throw UserException::signinUserException($credentials);
                }
            }

            /**
             * If provider doesnt return sessionId and sessionHandleKey.
             * Signin terminated.
             * You should contact with platform's administrator.
             */
            if (!isset($scopeResult['sid']) || !isset($scopeResult['shk'])){
                throw UserException::providerForgetAboutSession($credentials);
            }
            if (empty($scopeResult['sid']) || empty($scopeResult['shk'])){
                throw UserException::providerForgetAboutSession($credentials);
            }
            if (!strlen((string) $scopeResult['sid']) || !strlen((string) $scopeResult['shk'])){
                throw UserException::providerForgetAboutSession($credentials);
            }

            $this->sessionInstance->initializeSession($scopeResult['sid'], $scopeResult['shk']);

            GlobalRequest::setSessionIdCookie($scopeResult['sid']);
            GlobalRequest::setSessionHandleKeyCookie($scopeResult['shk']);

            return self::OK;
        }
        else{
            throw UserException::signinUserException($credentials);
        }
    }

    /**
     * Wake ups user
     */
    public function wakeup()
    {
        /**
         * Wakeup user is only available when user's session is alive
         */
        if ($this->isOnline()){
            $scope = new Scope($this->scopeName, 200);
            $scope->on('@users.wakeupUserById', [
                '@userID' => $this->sessionInstance->getUserId()
            ]);

            if ($scope->isOk()){
                $scopeResult = $scope->getResult();

                $this->verifyUserObject($scopeResult);

                $this->userID = $scopeResult['userID'];
                $this->userName = $scopeResult['username'];
                $this->userEmailAddress = $scopeResult['email'];
                $this->currentUserGroupId = $scopeResult['userGroup'];
                $this->userFlags = $scopeResult['flags'];
                $this->registerTime = intval($scopeResult['registerTime']);
                $this->registerIpAddress = intval($scopeResult['registerIPAddress']);
                $this->avatarUri = $scopeResult['avatarUri'];

                if (empty($scopeResult['confirmed'])){
                    $this->confirmed = false;
                }
                else{
                    $this->confirmed = $scopeResult['confirmed'];
                }
            }
        }
    }

    /**
     * Verifies fetched user object
     * @param array $userObject
     * @return bool
     * @throws UserException
     */
    protected function verifyUserObject(array $userObject)
    {
        if (isset($userObject['userID']) && isset($userObject['username']) &&
            isset($userObject['email']) && isset($userObject['userGroup'])
            && array_key_exists('flags', $userObject) && array_key_exists('registerTime', $userObject)
            && array_key_exists('registerIPAddress', $userObject) && array_key_exists('recomendedBy', $userObject)
            && array_key_exists('avatarUri', $userObject) && array_key_exists('confirmed', $userObject)){

            if (!is_numeric($userObject['userID'])){
                throw UserException::invalidUserData('userID: ' . $userObject['userID']);
            }
            if ((int) $userObject['userID'] <= 0){
                throw UserException::invalidUserData('userID: ' . $userObject['userID']);
            }

            if (!strlen(strval($userObject['username']))){
                throw UserException::invalidUserData('username: ' . $userObject['username']);
            }

            if (!strlen(strval($userObject['email']))){
                throw UserException::invalidUserData('email: ' . $userObject['email']);
            }

            if (!strlen(strval($userObject['userGroup']))){
                throw UserException::invalidUserData('userGroup: ' . $userObject['userGroup']);
            }

            return true;
        }
        else{
            throw UserException::invalidUserObject();
        }
    }

    /**
     * Logouts user
     * @param bool $dropCookies
     * @return bool
     */
    public function logout(bool $dropCookies = false)
    {
        $dropSessionResult = $this->sessionInstance->dropSession();


        if ($dropCookies === true){
            $this->sessionInstance->dropCookies();
        }
        else{
            return $dropSessionResult;
        }
    }

    public function register(string $userName, string $emailAddress, string $plainPassword)
    {
        /**
         * Checks if email address has invalid format
         */
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)){
            return self::INVALID_EMAIL_ADDRESS;
        }

        /** User name cannot be empty */
        if (!strlen($userName)){
            return self::EMPTY_USERNAME;
        }

        /** User's password cannot be empty */
        if (!strlen($plainPassword)){
            return self::EMPTY_USERPASSWORD;
        }

        $registerIPAddress = GlobalRequest::getUserIpAddress();


        $scope = new Scope(null, 200);
        $scope->on('@users.createUser', [
            '@username' => $userName,
            '@plainPassword' => $plainPassword,
            '@email' => $emailAddress,
            '@regip' => $registerIPAddress
        ]);

        if($scope->isOk()){
            $scopeResult = $scope->getResult();

            if (array_key_exists('username_format', $scopeResult)){
                return self::INVALID_USER_NAME;
            }

            if (array_key_exists('password_format', $scopeResult)){
                return self::INVALID_PASSWORD;
            }

            if (array_key_exists('email_format', $scopeResult)){
                return self::INVALID_EMAIL_ADDRESS;
            }

            if (array_key_exists('result', $scopeResult)){
                if ($scopeResult['result'] === 'username-not-available'){
                    return self::RESERVED_USER_NAME;
                }

                if ($scopeResult['result'] === 'email-not-available'){
                    return self::RESERVED_EMAIL_ADDRESS;
                }

                if ($scopeResult['result'] === false){
                    return false;
                }

                if ($scopeResult['result'] === 'prepare-confirm-code-failed'){
                    return self::PREPARE_CONFIRMATION_FAILED;
                }

                if ($scopeResult['result'] === 'unexpected-error-occurred'){
                    return self::UNEXPECTED_ERROR;
                }

                if ($scopeResult['result'] === 'account-confirmation-mail-sent'){
                    return true;
                }

                if ($scopeResult['result'] === 'send-mail-failed'){
                    return self::MAIL_SEND_FAILED;
                }

            }

        }

        return self::UNEXPECTED_ERROR;
    }

    /**
     * Gets user's id
     * @return int
     */
    public function getUserId()
    {
        return $this->userID;
    }

    /**
     * Gets user's name
     * @return null|string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Gets email address assigned to user account
     * @return null|string
     */
    public function getEmail()
    {
        return $this->userEmailAddress;
    }

    /**
     * Gets user's current group id
     * @return null|string
     */
    public function getUserGroupId()
    {
        return $this->currentUserGroupId;
    }

    /**
     * Gets user's register timestamp
     * @return int
     */
    public function getRegisterTime()
    {
        return $this->registerTime;
    }

    /**
     * Gets user's register ip address
     * @return null|string
     */
    public function getRegisterIpAddress()
    {
        return $this->registerIpAddress;
    }

    /**
     * Gets current user's avatar url
     * @return null|string
     */
    public function getAvatarUrl()
    {
        return $this->userAvatarUrl;
    }

    /**
     * Checks if user account is confirmed
     * @return bool
     */
    public function isAccountConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Checks if user has defined flag
     * @param string $flag
     * @return bool
     */
    public function hasFlag(string $flag) : bool
    {
        $flags = explode('', $flag);

        return in_array($flag, $flags);
    }

    /**
     * Destructs scope
     */
    private function destructScope()
    {
        $scope = new Scope($this->scopeName);
        $scope->compileName([
            '@userID' => $this->userID
        ]);
        $scope->drop();
    }

    /**
     * Updates user's name
     * @param string $newUsername
     * @return bool|int
     * @throws UserException
     */
    public function updateUserName(string $newUsername)
    {
        if ($this->isOnline()){
            if ($this->userName === $newUsername){
                return self::THE_SAME_NAME;
            }

            $this->destructScope();

            return $this->userGenericInstance->updateUserName($this->userID, $newUsername);
        }
        else{
            throw UserException::updateActionUserOffline($this->userID, 'username');
        }
    }

    /**
     * Updates email address assigned to user's account
     * @param string $newEmailAddress
     * @return int
     * @throws UserException
     */
    public function updateEmailAddress(string $newEmailAddress)
    {
        if ($this->isOnline()){
            if ($this->userEmailAddress === $newEmailAddress){
                return self::THE_SAME_EMAIL;
            }
            return $this->userGenericInstance->updateUserEmailAddress($this->userID, $newEmailAddress);
        }
        else{
            throw UserException::updateActionUserOffline($this->userID, 'email');
        }
    }


    /**
     * Confirms account
     * @param string $confirmCode
     * @return bool|int|UserException
     * @throws UserException
     */
    public function confirmAccount(string $confirmCode)
    {
        if ($this->isOnline()){
            if ($this->isAccountConfirmed()){
                return UserException::accountAlreadyConfirmed($this->userID);
            }

            $this->destructScope();

            return $this->userGenericInstance->confirmUserAccount($this->userID, $confirmCode);
        }
        else{
            throw UserException::updateActionUserOffline($this->userID, 'confirm_account');
        }
    }

    /**
     * First stage of password change.
     * User will receive an email with authorize code.
     * This code will be used to finish password change.
     * @param string $password
     * @return bool|int
     * @throws UserException
     */
    public function beginPasswordChange(string $password)
    {
        if ($this->isOnline()){
            return $this->userGenericInstance->beginPasswordChange($this->userID, $password);
        }

        throw UserException::updateActionUserOffline($this->userID, 'begin_password_change');
    }

    /**
     * The last stage of password change.
     * Accepts changes.
     * @param string $authorizeCode
     * @return bool|int
     * @throws UserException
     */
    public function finishPasswordChange(string $authorizeCode)
    {
        if ($this->isOnline()){
            $this->destructScope();

            return $this->userGenericInstance->finishPasswordChange($this->userID, $authorizeCode);
        }

        throw UserException::updateActionUserOffline($this->userID, 'finish_password_change');
    }

    /**
     * First step of change username.
     * Sends email with authorization code
     * @param string $username
     * @return bool|int
     * @throws UserException
     */
    public function beginUsernameChange(string $username)
    {
        if ($this->isOnline()){
            if ($this->userName === $username){
                return self::THE_SAME_NAME;
            }

            return $this->userGenericInstance->beginUsernameChangeProcess($this->userID, $username);
        }

        throw UserException::updateActionUserOffline($this->userID, 'begin_username_change');
    }

    /**
     * The last step of process to change username.
     * @param string $authorizeCode
     * @return bool|int
     * @throws UserException
     */
    public function finishUsernameChange(string $authorizeCode)
    {
        if ($this->isOnline()){
            $this->destructScope();

            $this->destructScope();

            return $this->userGenericInstance->finishUserNameChangeProcess($this->userID, $authorizeCode);
        }

        throw UserException::updateActionUserOffline($this->userID, 'finish_username_change');
    }

    /**
     * Creates an ask for email address change
     * @param string $newEmailAddress
     * @return bool|int
     * @throws UserException
     */
    public function createAskEmailAddressChange(string $newEmailAddress)
    {
        if ($this->isOnline()){
            return $this->userGenericInstance->createAskChangeEmailAddress($this->userID, $newEmailAddress);
        }

        throw UserException::updateActionUserOffline($this->userID, 'ask_emailaddress_change');
    }


    /**
     * Accepts ask for email address change
     * @param string $authorizeCode
     * @return bool|int
     * @throws UserException
     */
    public function acceptAskEmailAddressChange(string $authorizeCode)
    {
        if ($this->isOnline()){
            return $this->userGenericInstance->acceptAskChangeEmailAddress($this->userID, $authorizeCode);
        }

        throw UserException::updateActionUserOffline($this->userID, 'accept_ask_emailaddress_change');
    }

    /**
     * Finishes email address change
     * @param string $authorizeCode
     * @return bool|int
     * @throws UserException
     */
    public function finishEmailAddressChange(string $authorizeCode)
    {
        if ($this->isOnline()){
            $this->destructScope();

            return $this->userGenericInstance->finishChangeEmailAddress($this->userID, $authorizeCode);
        }

        throw UserException::updateActionUserOffline($this->userID, 'finish_emailaddress_change');
    }

    /**
     * Verifies user's password
     * @param string $plainPassword
     * @return bool|int
     * @throws UserException
     */
    public function verifyUserPassword(string $plainPassword)
    {
        if ($this->isOnline()){
            if (!strlen($plainPassword)){
                return self::EMPTY_USERPASSWORD;
            }

            $scope = new Scope();
            $scope->on('@users.verifyUserPassword', [
                '@userID' => $this->userID,
                '@plainPassword' => $plainPassword
            ]);

            if ($scope->isOk()){
                $scopeResult = $scope->getResult();
                if (array_key_exists('result', $scopeResult)){
                    if ($scopeResult['result'] === true){
                        return true;
                    }
                    else if ($scopeResult['result'] === false){
                        return false;
                    }
                }
            }

            return self::UNEXPECTED_ERROR;
        }

        throw UserException::updateActionUserOffline($this->userID, 'verify_userpassword');
    }

    /**
     * Changes user avatar url
     * @param string $newAvatarUrl
     * @return bool|int
     * @throws UserException
     */
    public function changeAvatar(string $newAvatarUrl)
    {
        if ($this->isOnline()){
            if (!strlen($newAvatarUrl)){
                return self::EMPTY_AVATAR_URL;
            }

            $this->destructScope();
            return $this->userGenericInstance->changeUserAvatarUrl($this->userID, $newAvatarUrl);
        }

        throw UserException::updateActionUserOffline($this->userID, 'change_avatar_url');
    }

    /**
     * Drops user avatar
     * @return bool|int
     * @throws UserException
     */
    public function dropAvatar()
    {
        if ($this->isOnline()){
            $this->destructScope();
            return $this->userGenericInstance->dropUserAvatar($this->userID);
        }

        throw UserException::updateActionUserOffline($this->userID, 'drop_user_avatar');
    }

    /**
     * Resends confirm code
     * @param null $newEmailAddress
     * @return bool|int
     * @throws UserException
     */
    public function resendConfirmCode($newEmailAddress = null)
    {
        $emailAddress = (!empty($newEmailAddress) ? $newEmailAddress : $this->userEmailAddress);

        if ($this->isOnline()){
            return $this->userGenericInstance->resendConfirmCode($this->userID, $emailAddress);
        }

        throw UserException::updateActionUserOffline($this->userID, 'resend_confirm_code');
    }

    /**
     * Reminds username
     * @param string $emailAddress
     * @return bool|int
     */
    public function remindUsername(string $emailAddress)
    {
        return $this->userGenericInstance->remindUsername($emailAddress);
    }

    /**
     * Reminds user's password
     * @param string $emailAddress
     * @return bool|int
     */
    public function remindPassword(string $emailAddress)
    {
        return $this->userGenericInstance->remindPassword($emailAddress);
    }

    /**
     * Gets XAUser generic instance
     * @return XAUserGeneric
     */
    public function getGeneric() : XAUserGeneric
    {
        return $this->userGenericInstance;
    }


}

?>
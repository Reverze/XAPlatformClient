<?php

namespace XA\PlatformClient\Controller\Session;

use XA\PlatformClient\Controller\Session\Exception\SessionException;
use XA\PlatformClient\Dist\Scope;
use XA\PlatformClient\Request\GlobalRequest;
use XA\PlatformClient\Request\RequestDefinitions;

class XASession
{
    /**
     * Id of current session instance
     * @var string|null
     */
    protected $sessionId = null;

    /**
     * HandleKey of current session instance
     * @var string|null
     */
    protected $sessionHandleKey = null;

    /**
     * UserAgent assigned to session instance
     * @var string|null
     */
    protected $userAgent = null;

    /**
     * Client's ip address assigned to session instance
     * @var string|null
     */
    protected $ipAddress = null;

    /**
     * Session start time
     * @var int
     */
    protected $startTime = 0;

    /**
     * Session last active time
     * @var int
     */
    protected $sessionLastActiveTime = 0;

    /**
     * Session lifetime
     * @var int
     */
    protected $sessionLifetime = 0;

    /**
     * Session instance owner
     * @var int
     */
    protected $userID = 0;

    /**
     * Scope's name.
     * This scope stores session object instance.
     * It relies on parameter 'sessionId' passed to scope.
     * @var string
     */
    private $sessionInstanceScopeName = "session_object_{sessionID}";

    /**
     * Scope's name.
     * This scope stores session tick result.
     * It relies on parameter 'sessionId' passed to scope.
     * @var string
     */
    private $sessionTickScopeName = "session_tick_{sessionID}";

    public function __construct()
    {
        /**
         * Wake ups session for current user.
         */
        $this->wakeupSession();

    }

    /**
     * Wake ups session.
     * Loads session instance from cache or platform.
     * If session instance is not exists, does nothing.
     * If error occurred while fetching session instance (it does no matter
     * if exists or not), for example: cache error or provider error
     * throws an exception.
     * @throws SessionException
     */
    private function wakeupSession()
    {
        /**
         * Reads session id from cookie
         */
        $sessionId = GlobalRequest::getSessionIdFromCookie();
        /**
         * Reads session handle key from cookie
         */
        $sessionHandleKey = GlobalRequest::getSessionHandleKeyFromCookie();

        if (!strlen($sessionId) || !strlen($sessionHandleKey)){
            return;
        }

        $scope = new Scope($this->sessionInstanceScopeName, 300);
        $scope->on('@sessions.getSession', [
            '@sessionID' => $sessionId,
            '@sessionHandleKey' => $sessionHandleKey
        ]);


        /**
         * If query was ok (from cache or provider)
         */
        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            /**
             * If scope result contains result => false, it means
             * that session instance was not found.
             * Invalid sessionId, handlekey etc.
             * If session instance was found, scope result
             * doesn't contain key 'result'.
             */
            if (!array_key_exists('result', $scopeResult)){
                /**
                 * All following session instance parameters are required to verify
                 * session.
                 */
                if (isset($scopeResult['session_id']) && isset($scopeResult['session_handle_key']) &&
                    isset($scopeResult['user_agent']) && isset($scopeResult['ip_address']) &&
                    isset($scopeResult['start_time']) && isset($scopeResult['last_active']) &&
                    isset($scopeResult['lifetime']) && isset($scopeResult['userID'])){



                    $this->sessionId = (string) $scopeResult['session_id'];
                    $this->sessionHandleKey = (string) $scopeResult['session_handle_key'];
                    $this->userAgent = (string) $scopeResult['user_agent'];
                    $this->ipAddress = (string) $scopeResult['ip_address'];
                    $this->startTime = (int) $scopeResult['start_time'];
                    $this->sessionLastActiveTime = (int) $scopeResult['last_active'];
                    $this->sessionLifetime = (int) $scopeResult['lifetime'];
                    $this->userID = (int) $scopeResult['userID'];
                }
            }

        }

    }

    /**
     * Checks if session is alive
     * @return bool
     */
    public function isAlive() : bool
    {
        /**
         * If session details has been fetched.
         */
        if (!empty($this->sessionId) && !empty($this->sessionHandleKey)
            && !empty($this->userAgent) && !empty($this->ipAddress)){

            /**
             * At platform client we only compare four parameters:
             * Session instance is identified by:
             *  sessionId, sessionHandleKey, userAgent and ipAddress.
             * If someting current parameters are not match to session instance details,
             * session is not alive
             */

            /**
             * Gets session parameters, sent by client
             */
            $clientSessionId = GlobalRequest::getSessionIdFromCookie();
            $clientSessionHandleKey = GlobalRequest::getSessionHandleKeyFromCookie();
            $clientUserAgent = GlobalRequest::getUserAgent();
            $clientIpAddress = GlobalRequest::getUserIpAddress();




            /**
             * If one of client's variables are not defined,
             * return false
             */
            if (empty($clientSessionId)) return false;
            if (empty($clientSessionHandleKey)) return false;
            if (empty($clientUserAgent)) return false;
            if (empty($clientIpAddress)) return false;



            if ($this->sessionId !== $clientSessionId){
                return false;
            }

            if ($this->sessionHandleKey !== $clientSessionHandleKey){
                return false;
            }
            if ($this->userAgent !== $clientUserAgent){
                return false;
            }
            if ($this->ipAddress !== $clientIpAddress){
                return false;
            }


            /**
             * At this point we know that client's variable points to the same session instance.
             * The next stage is to check if session is not expired.
             */

            /**
             * Gets current timestamp in seconds on server.
             * Provider should return timestamp in your server's timezone.
             */
            $currentTimestamp = time();

            /**
             * If session last active timestamp is not defined,
             * returns false
             */
            if (empty($this->sessionLastActiveTime) ||
                $this->sessionLastActiveTime === 0){
                return false;
            }

            /**
             * Session end time if extended every time when you do request on alive session.
             * Session can have "infinity" lifetime if user makes requests often.
             * Its simple. To calculate session expire timestamp, we add session's lifetime
             * to session last active timestamp.
             */
            $sessionEndTimestamp = (int) $this->sessionLastActiveTime + (int) $this->sessionLifetime;



            if ($currentTimestamp > $sessionEndTimestamp){
                return false;
            }

            /**
             * Everything seems to be ok, so we return true - session is alive, hurra :)
             */
            $this->tickSession();

            return true;
        }
        /**
         * If session details are not set, returns false - session is not alive
         */
        else{
            return false;
        }
    }


    /**
     * Initializes session object from given sessionId and sessionHandleKey.
     * If you have sessionId and sessionHandleKey you can initialize session object.
     * Provider sent this two variables after user signin. After that you should
     * get more details about session instance and store it for later use.
     * @param string $sessionId
     * @param string $sessionHandleKey
     */
    public function initializeSession(string $sessionId, string $sessionHandleKey)
    {
        if (!strlen($sessionId) || !strlen($sessionHandleKey)){
            return false;
        }

        $scope = new Scope($this->sessionInstanceScopeName, 300);
        $scope->on('@sessions.getSession', [
            '@sessionID' => $sessionId,
            '@sessionHandleKey' => $sessionHandleKey
        ]);



        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            /**
             * We dont care about validity.
             */
            $this->sessionId = $scopeResult['session_id'] ?? null;
            $this->sessionHandleKey = $scopeResult['session_handle_key'] ?? null;
            $this->userAgent = $scopeResult['user_agent'] ?? null;
            $this->ipAddress = $scopeResult['ip_address'] ?? null;
            $this->startTime = $scopeResult['start_time'] ?? 0;
            $this->sessionLastActiveTime = $scopeResult['last_active'] ?? 0;
            $this->sessionLifetime = $scopeResult['lifetime'] ?? 0;
            $scope->store();
        }
    }

    /**
     * Ticks session instance - extends session end time.
     */
    protected function tickSession()
    {
        if (!empty($this->sessionId) && !empty($this->sessionHandleKey) && ($this->userID > 0)){
            $scope = new Scope($this->sessionTickScopeName, 200);
            $scope->on('@users.tickSession', [
                '@sessionID' => $this->sessionId,
                '@sessionHandleKey' => $this->sessionHandleKey,
                '@userID' => $this->userID
            ]);

            /**
             * We don't care about response
             */
        }
    }

    /**
     * Drops session instance
     * @return bool
     * @throws SessionException
     */
    public function dropSession() : bool
    {
        if (!empty($this->sessionId) && !empty($this->sessionHandleKey) && ($this->userID > 0)){
            $scope = new Scope(null, 200);
            $scope->on('@sessions.dropSingleSession', [
                '@sessionID' => $this->sessionId,
                '@sessionHandleKey' => $this->sessionHandleKey,
                '@userID' => $this->userID
            ]);


            if ($scope->isOk()){
                $scopeResult = $scope->getResult();

                if (array_key_exists('result', $scopeResult)){
                    if ($scopeResult['result'] === true){
                        return true;
                    }
                    /**
                     * If session expired etc.
                     */
                    else if ($scopeResult['result'] === false){
                        return false;
                    }
                    /**
                     * Internal provider error
                     */
                    else if ($scopeResult['result'] === 'unexpected-error-occurred'){
                        throw SessionException::unexpectedErrorOccurredWhileDropSession();
                    }
                }
            }
            /**
             * Not authorized etc.
             */
            else{
                return false;
            }

        }
        /**
         * Session is not alive etc
         */
        else{
            return false;
        }
    }

    /**
     * Drops cookies.
     * Be aware while using it. Body response cannot be sent before calling this method.
     */
    public function dropCookies()
    {
        GlobalRequest::dropSessionIdCookie();
        GlobalRequest::dropSessionHandleKeyCookie();
    }

    /**
     * Gets userId. (session owner).
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userID;
    }

    /**
     * Gets sessionId
     * @return null|string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Gets session handle key
     * @return null|string
     */
    public function getSessionHandleKey()
    {
        return $this->sessionHandleKey;
    }



}

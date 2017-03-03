<?php

namespace XA\PlatformClient\Controller\User;

class XAUserVirtual
{
    /**
     * Virtual user'ID
     * @var integer
     */
    private $userID = null;

    /**
     * Virtual user's name
     * @var string
     */
    private $userName = null;

    /**
     * Virtual user's email address
     * @var string
     */
    private $emailAddress = null;

    /**
     * Virtual user's groupid
     * @var string
     */
    private $userGroupId = null;

    /**
     * Virtual user registration timestamp
     * @var int
     */
    private $registerTimestamp = 0;

    /**
     * Virtual user registration ip address
     * @var string
     */
    private $registerIPAddress = null;

    /**
     * Virtual user recommended by userID
     * @var int
     */
    private $recomendedBy = 0;

    /**
     * Virtual user's avatar url
     * @var string|null
     */
    private $avatarUrl = null;

    /**
     * Virtual user's account confirmation status
     * @var bool
     */
    private $accountConfirmed = false;

    public function __construct(array $userArray = array())
    {
        $this->compileFromArray($userArray);
    }

    /**
     * Compiles virtual user from array
     * @param array $userArray
     */
    private function compileFromArray(array $userArray)
    {
        if (array_key_exists('userID', $userArray)){
            if (is_numeric($userArray['userID'])){
                $this->userID = (int) $userArray['userID'];
            }
        }

        if (array_key_exists('username', $userArray)){
            if (is_string($userArray['username']) && strlen($userArray['username'])){
                $this->userName = (string) $userArray['username'];
            }
        }

        if (array_key_exists('email', $userArray)){
            if (is_string($userArray['email']) && strlen($userArray['email'])){
                if (filter_var($userArray['email'], FILTER_VALIDATE_EMAIL)){
                    $this->emailAddress = (string) $userArray['email'];
                }
            }
        }

        if (array_key_exists('userGroup', $userArray)){
            if (is_string($userArray['userGroup']) && strlen($userArray['userGroup'])){
                $this->userGroupId = (string) $userArray['userGroup'];
            }
        }

        if (array_key_exists('registerTime', $userArray)){
            if (is_numeric($userArray['registerTime'])){
                $this->registerIPAddress = (int) $userArray['registerTime'];
            }
        }

        if (array_key_exists('registerIPAddress', $userArray)){
            if (is_string($userArray['registerIPAddress']) && strlen($userArray['registerIPAddress'])){
                if (filter_var($userArray['registerIPAddress'], FILTER_VALIDATE_IP)){
                    $this->registerIPAddress = (string) $userArray['registerIPAddress'];
                }
            }
        }

        if (array_key_exists('recomendedBy', $userArray)){
            if (is_numeric($userArray['recomendedBy'])){
                if (intval($userArray['recomendedBy']) > 0){
                    $this->recomendedBy = (int) $userArray['recomendedBy'];
                }
            }
        }

        if (array_key_exists('avatarUri', $userArray)){
            if (is_string($userArray['avatarUri']) && strlen($userArray['avatarUri'])){
                if (filter_var($userArray['avatarUri'], FILTER_VALIDATE_URL)){
                    $this->avatarUrl = $userArray['avatarUri'];
                }
            }
        }

        if (array_key_exists('confirmed', $userArray)){
            if (is_bool($userArray['confirmed'])){
                $this->accountConfirmed = (bool) $userArray['confirmed'];
            }
        }
    }

    /**
     * Gets virtual userID
     * @return int
     */
    public function getUserID() : int
    {
        return empty($this->userID) ? 0 : $this->userID;
    }

    /**
     * Gets virtual user's name
     * @return string
     */
    public function getUserName() : string
    {
        return empty($this->userName) ? "" : $this->userName;
    }

    /**
     * Gets virtual user's email address
     * @return string
     */
    public function getEmailAddress() : string
    {
        return empty($this->emailAddress) ? "" : $this->emailAddress;
    }

    /**
     * Gets virtual user's groupid
     * @return string
     */
    public function getUserGroupId() : string
    {
        return empty($this->userGroupId) ? "" : $this->userGroupId;
    }

    /**
     * Gets virtual user's registration timestamp
     * @return int
     */
    public function getRegisterTimestamp() : int
    {
        return empty($this->registerTimestamp) ? 0 : $this->registerTimestamp;
    }

    /**
     * Gets virtual user's registration ip address
     * @return string
     */
    public function getRegisterIpAddress() : string
    {
        return empty($this->registerIPAddress) ? "" : $this->registerIPAddress;
    }

    public function getRecommendedBy() : int
    {
        return empty($this->recomendedBy) ? 0 : $this->recomendedBy;
    }

    /**
     * Gets virtual user's avatar url
     * @return string
     */
    public function getAvatarUrl() : string
    {
        return empty($this->avatarUrl) ? "" : $this->avatarUrl;
    }

    /**
     * Checks if virtual user's account is confirmed.
     * @return bool
     */
    public function isAccountConfirmed() : bool
    {
        return empty($this->accountConfirmed) ? false : $this->accountConfirmed;
    }


}

?>
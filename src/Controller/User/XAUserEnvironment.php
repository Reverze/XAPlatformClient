<?php

namespace XA\PlatformClient\Controller\User;

use XA\PlatformClient\Dist\Scope;


class XAUserEnvironment
{
    /**
     * Scope's name.
     * This scope stores requirements for usernames and passwords.
     * @var string
     */
    private $userEnvironmentScopeName = 'xa_user_enviroment';

    /**
     * Requirements for user's passwords.
     * @var array
     */
    private $userPasswordRequirements = [];

    /**
     * Requirements for usernames
     * @var array
     */
    private $userNameRequirements = [];

    public function __construct()
    {
        $this->loadEnvironment();
    }

    protected function loadEnvironment()
    {
        $scope = new Scope($this->userEnvironmentScopeName, 400);
        $scope->on('@users.env.getCredParams', []);

        if ($scope->isOk()){
            $scopeResult = $scope->getResult();

            /**
             * If value under key 'password|username' is exists => must be array
             */
            if (isset($scopeResult['password']) && is_array($scopeResult['password'])){
                $this->loadPasswordRequirementsFrom($scopeResult['password']);
            }

            if (isset($scopeResult['username']) && is_array($scopeResult['username'])){
                $this->loadUsernameRequirementsFrom($scopeResult['username']);
            }

        }
    }

    /**
     * Loads password requirements from array
     * @param array $passwordRequirements
     */
    protected function loadPasswordRequirementsFrom(array $passwordRequirements)
    {
        /**
         * If password's minimum length is defined
         */
        if (array_key_exists('minimumLength', $passwordRequirements)){
            if (is_numeric($passwordRequirements['minimumLength'])){
                $this->userPasswordRequirements['minLength'] = abs(intval($passwordRequirements['minimumLength']));
            }
        }
        else{
            $this->userPasswordRequirements['minLength'] = 4;
        }

        /**
         * Determines if password must contain digits
         */
        if (array_key_exists('requireDigits', $passwordRequirements)){
            if (is_bool($passwordRequirements['requireDigits'])){
                $this->userPasswordRequirements['requireDigits'] = (bool) $passwordRequirements['requireDigits'];
            }
        }
        else{
            $this->userPasswordRequirements['requireDigits'] = false;
        }
    }

    /**
     * Loads usernames requirements from array
     * @param array $usernameRequirements
     */
    protected function loadUsernameRequirementsFrom(array $usernameRequirements)
    {
        /**
         * If minimal username length is defined
         */
        if (array_key_exists('minimumLength', $usernameRequirements)){
            if (is_numeric($usernameRequirements['minimumLength'])){
                $this->userNameRequirements['minLength'] = abs(intval($usernameRequirements['minimumLength']));
            }
        }
        else{
            $this->userNameRequirements['minLength'] = 6;
        }

        /**
         * If maximal username length is defined
         */
        if (array_key_exists('maximumLength', $usernameRequirements)){
            if (is_numeric($usernameRequirements['maximumLength'])){
                $this->userNameRequirements['maxLength'] = abs(intval($usernameRequirements['maximumLength']));
            }
        }
        else{
            $this->userNameRequirements['maxLength'] = 20;
        }


        if (array_key_exists('containsAlpha', $usernameRequirements)){
            if (is_bool($usernameRequirements['containsAlpha'])){
                $this->userNameRequirements['containsAlpha'] = (bool) $usernameRequirements['containsAlpha'];
            }
        }
        else{
            $this->userNameRequirements['containsAlpha'] = true;
        }
    }

    /**
     * Gets minimal usersname length
     * @return int
     */
    public function getMinimalUserNameLength() : int
    {
        return $this->userNameRequirements['minLength'] ?? 6;
    }

    /**
     * Gets maximal username length
     * @return int
     */
    public function getMaximulUserNameLength() : int
    {
        return $this->userNameRequirements['maxLength'] ?? 20;
    }

    /**
     * Checks if username should contains alpha
     * @return bool
     */
    public function shouldUserNameContainAlpha() : bool
    {
        return $this->userNameRequirements['containsAlpha'] ?? true;
    }

    /**
     * Gets minimal userpassword length
     * @return int
     */
    public function getMinimalPasswordLength() : int
    {
        return $this->userPasswordRequirements['minLength'] ?? 4;
    }

    /**
     * Checks if userpassword should contains digits
     * @return bool
     */
    public function shouldPasswordContainDigits() : bool
    {
        return $this->userPasswordRequirements['requireDigits'] ?? false;
    }

}

?>
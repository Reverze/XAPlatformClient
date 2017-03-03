<?php
/**
 * @Author Reverze (hawkmedia24@gmail.com)
 * This file is part of XAPlatformClient.
 *
 * This module allows you to prepare valid connection credentials to XAPlatformServer.
 */

namespace XA\PlatformClient\Auth;

use XA\PlatformClient\Auth\Exception\AuthException;

class PlatformCredentials
{
    /**
     * Service hostname
     * @var string
     */
    private $serviceHost = null;

    /**
     * Service port
     * @var integer
     */
    private $servicePort = 3215;

    /**
     * App key identifier
     * @var string
     */
    private $appKey = null;


    public function __construct()
    {

    }

    /**
     * Sets provider hostname
     * @param string $provider
     * @throws \XA\PlatformClient\Auth\Exception\AuthException
     */
    public function setProvider(string $provider)
    {
        /**
         * Provider's hostname cannot be empty.
         * Is given provider's hostname is empty throw an exception
         */
        if (!strlen($provider)){
            throw AuthException::emptyProvider();
        }

        if (empty($this->serviceHost)){
            $this->serviceHost = $provider;
        }
    }

    /**
     * Sets custom provider port. Default provider listen on port 3215.
     * @param int $providerPort
     * @throws AuthException
     */
    public function setPort(int $providerPort)
    {
        /**
         * Not fall within the ports scope
         */
        if ($providerPort < 0 || $providerPort > 65535){
            throw AuthException::invalidPortRange();
        }

        $this->servicePort = $providerPort;
    }

    /**
     * Sets application key identifier. Please ask server administrator for key assigned to your app.
     * @param string $appKey
     * @throws AuthException
     */
    public function setAppKey(string $appKey)
    {
        if (!strlen($appKey)){
            throw AuthException::emptyAppKey();
        }

        if (empty($this->appKey)){
            $this->appKey = $appKey;
        }
    }

    /**
     * Gets provider credentials as array
     * @return array
     */
    public function getCredentials() : array
    {
        return [
            "providerHost" => $this->serviceHost,
            "providerPort" => $this->servicePort,
            "appKey" => $this->appKey
        ];
    }



}

?>
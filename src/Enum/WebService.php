<?php

namespace XA\PlatformClient\Enum;

final class WebService
{
    /**
     * External webservice is not available
     */
    const NOT_AVAILABLE = 1100;
    /**
     * External resource was not found
     */
    const RESOURCE_NOT_FOUND = 1101;
    /**
     * External resource is not available
     */
    const RESOURCE_NOT_AVAILABLE = 1102;

    private function __construct()
    {

    }
}

?>
<?php

namespace XA\PlatformClient\Rest;

use Webmozart\Json\DecodingFailedException;
use Webmozart\Json\JsonDecoder;

class QueryResult
{
    const QUERY_OK = 200;
    const QUERY_NOT_FOUND = 404;
    const QUERY_NOT_AUTHORIZED = 403;
    const QUERY_BAD = 400;

    /**
     * Query result as array
     * @var array
     */
    private $resultArray = array();

    /**
     * Query result code (equivalent to http response code)
     * @var integer
     */
    private $queryResultCode = null;

    public function __construct()
    {

    }

    /**
     * Sets query result
     * @param $result
     */
    public function setQueryResult($result)
    {
        /**
         * If result is an array
         */
        if (is_array($result)){
            $this->resultArray = $result;
        }
        else if (is_string($result)) {
            $decoder = new JsonDecoder();
            $decoder->setObjectDecoding(JsonDecoder::ASSOC_ARRAY);

            try {
                $this->resultArray = $decoder->decode($result);
            }
            catch (DecodingFailedException $ex) {
                throw $ex;
            }
        }
    }

    /**
     * Sets query status code
     * @param int $responseCode
     */
    public function setQueryStatusCode(int $responseCode)
    {
        $this->queryResultCode = $responseCode;
    }

    /**
     * Gets query status
     * @return int
     */
    public function getQueryStatus() : int
    {
        return $this->queryResultCode;
    }

    /**
     * Gets result as array
     * @return array
     */
    public function getResult() : array
    {
        return $this->resultArray;
    }

    /**
     * Check if query is ok
     * @return array
     */
    public function isOk()
    {
        return ($this->queryResultCode === self::QUERY_OK);
    }



}

?>
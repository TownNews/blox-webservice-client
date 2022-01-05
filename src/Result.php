<?php

declare(strict_types=1);

namespace Townnews\BLOX\Webservice;

use Psr\HTTP\Message\ResponseInterface;
use Exception\ResultException;

/**
 * Result from a BLOX webservice API call
 * 
 * @author Patrick O'Lone <polone@townnews.com>
 * @copyright TownNews.com 2022
 * @license MIT
 */
class Result
{
    private ResponseInterface $oResponse;
    private \stdClass $oPayload;

    public function __construct(ResponseInterface $oResponse)
    {
        $this->oResponse = $oResponse;
        $this->oPayload = json_decode((string) $oResponse->getBody(), false, 512, \JSON_THROW_ON_ERROR);
    }

    /**
     * Checks to see if the response is an error state
     * 
     * @return bool
     *  Returns `true` if the instance represents an error state
     */
    public function isError() : bool
    {
        return ($this->oResponse->getStatusCode() >= 400);
    }

    /**
     * The parsed payload
     * 
     * @return \stdClass
     *  Returns the JSON payload from the instance
     */
    public function getPayload() : \stdClass
    {
        return $this->oPayload;
    }

    /**
     * Returns the original PSR7 HTTP response
     * 
     * @return ResponseInterface
     *  The PSR7 HTTP response from the API call
     */
    public function getResponse() : ResponseInterface
    {
        return $this->oResponse;
    }

    /**
     * Create an exception instance based on payload
     * 
     * @return ResultException
     *  An exception from the API call
     */
    public function toException() : ResultException
    {
        return new ResultException(
            $this->oPayload->message ?? 'No error message was set',
            $this->oPayload->code ?? -1
        );
    }
}
<?php

declare(strict_types=1);

namespace Townnews\BLOX\Webservice;

use Psr\HTTP\Message\ResponseInterface;
use Exception\ResultException;

class Result
{
    private ResponseInterface $oResponse;
    private \stdClass $oPayload;

    public function __construct(ResponseInterface $oResponse)
    {
        $this->oResponse = $oResponse;
        $this->oPayload = json_decode((string) $oResponse->getBody(), false, 512, \JSON_THROW_ON_ERROR);
    }

    public function isError()
    {
        return ($this->oResponse->getStatusCode() >= 400);
    }

    public function getPayload()
    {
        return $this->oPayload;
    }

    public function getResponse() : ResponseInterface
    {
        return $this->oResponse;
    }

    public function toException() : ResultException
    {
        return new ResultException(
            $this->oPayload->message ?? 'No error message was set',
            $this->oPayload->code ?? -1
        );
    }
}
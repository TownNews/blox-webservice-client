<?php

declare(strict_types=1);

namespace Townnews\BLOX\Webservice;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\MultipartStream;

class Client
{
    private \GuzzleHttp\Client $oClient;

    public function __construct(array $kConfig = [])
    {
        // Mandatory configuration options

        foreach(['hostname', 'api_key', 'api_secret'] as $sKey) {
            if (empty($kConfig[$sKey])) {
                throw new Exception('Option `' . $sKey . '` must not be empty');
            }
        }

        // Configuration will set overridable defaults, followed by user
        // configuration via `guzzle` option, followed by forced options

        $kOpts = array_merge([
            'connect_timeout' => 3
        ],
        $kConfig['guzzle'] ?? [],
        [
            'base_uri' => 'https://' . $kConfig['hostname'] . '/tncms/webservice/v1/',
            'auth' => [
                $kConfig['api_key'],
                $kConfig['api_secret']
            ],
            'headers' => [
                'User-Agent' => $kConfig['user_agent'] ?? 'BLOX Webservice Client',
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip'
            ],
            'http_errors' => false
        ]);

        $this->oClient = new \GuzzleHttp\Client($kOpts);
    }

    public function get( string $sModule, string $sAction, array $kParams = []) : Result
    {
        $oResponse = $this->oClient->get($sModule . '/' . $sAction, [
            'query' => $kParams
        ]);

        return $this->handleResponse($oResponse);
    }

    public function post( string $sModule, string $sAction, array $kParams = []) : Result
    {
        // Build multipart message for POST so files can be uploaded as well

        $aParams = [];
        foreach($kParams as $sKey => $xValue) {

            $kField = [
                'name' => $sKey,
                'contents' => $xValue
            ];

            if ($xValue[0] == '@') {
                $xValue = substr($xValue, 1);
                if (is_file($xValue)) {
                    $kField['contents'] = Psr7\Utils::tryFopen($xValue, 'r');
                }
            }

            $aParams[] = $kField;

        }

        $oResponse = $this->oClient->post($sModule . '/' . $sAction, [
            'multipart' => $aParams
        ]);

        return $this->handleResponse($oResponse);
    }

    public function postAsJSON(string $sAction, string $sModule, $xPayload) : Result
    {
        $oResponse = $this->oClient->post($sModule . '/' . $sAction, [
            'json' => $xPayload
        ]);

        return $this->handleResponse($oResponse);
    }

    public function handleResponse(ResponseInterface $oResponse) : Result
    {
        $oResult = new Result($oResponse);
        if ($oResult->isError()) {
            throw $oResult->toException();
        }

        return $oResult;
    }
}
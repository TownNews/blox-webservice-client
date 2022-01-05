<?php

declare(strict_types=1);

namespace Townnews\BLOX\Webservice;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\MultipartStream;

/**
 * BLOX webservice client
 * 
 * This client can be used to perform API calls to a BLOX-powered
 * web site. Access will need to be granted by a site administrator
 * in order to perform API calls. To configure a client, you will
 * need:
 * 
 * - hostname: The host name of a BLOX site
 * - api_key: API key generated in the BLOX admin
 * - api_secret: API secret that goes along with the API key
 * 
 * @author Patrick O'Lone <polone@townnews.com>
 * @copyright 2022 TownNews.com
 * @license MIT
 */
class Client
{
    /** @var \GuzzleHttp\Client Internal instance of the HTTP client */
    private \GuzzleHttp\Client $oClient;

    /**
     * Constructor
     * 
     * @param array $kConfig
     *  An array of configuration options for the webservice client
     * 
     *  - hostname: The host name of a BLOX site
     *  - api_key: API key generated in the BLOX admin
     *  - api_secret: API secret that is paired with the API key
     *  - guzzle: An array of configuration options that will be passed
     *       to the internal \GuzzleHttp\Client instance. Some options
     *       cannot be overridden
     *  - user_agent: Override the default user agent that is passed
     */
    public function __construct(array $kConfig = [])
    {
        // Mandatory configuration options

        foreach(['hostname', 'api_key', 'api_secret'] as $sKey) {
            if (empty($kConfig[$sKey])) {
                throw new \Exception('Option `' . $sKey . '` must not be empty');
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

    /**
     * Perform a GET HTTP request to the API end-point
     * 
     * @return Result
     *  A wrapper containing the original response and an
     *  already parsed payload for immediate use
     * 
     * @param string $sModule
     *  The webservice module to perform requests against
     * 
     * @param string $sAction
     *  The webservice action to perform requests against
     * 
     * @param array $kParams
     *  The parameters to pass to the query string
     */
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

    private function handleResponse(ResponseInterface $oResponse) : Result
    {
        $oResult = new Result($oResponse);
        if ($oResult->isError()) {
            throw $oResult->toException();
        }

        return $oResult;
    }
}
<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

final class GetTest extends TestCase
{
    public function testSearchQuery(): void
    {
        $oSearchPayload = (object) [
            'total' => 1,
            'offset' => 0,
            'limit' => 10,
            'items' => [
                (object) [
                    'id' => '7b76f59a-9764-11e9-9055-000c29fd1fcf',
                    'title' => 'Test',
                    'type' => 'article',
                    'start_time' => '2019-06-25T16:15:00+0000',
                    'summary' => 'Test Summary'
                ]
            ]
        ];

        $oMock = new MockHandler([
            new Response(200, [], json_encode($oSearchPayload))
        ]);

        $oHandler = HandlerStack::create($oMock);
        $oClient = new \Townnews\BLOX\Webservice\Client([
            'hostname' => 'www.example.com',
            'api_key' => 'test',
            'api_secret' => 'secret',
            'guzzle' => [
                'handler' => $oHandler,
                'verify' => false
            ]
        ]);

        $oResult = $oClient->get('editorial', 'search', ['q' => 'test']);
        $this->assertEquals($oResult->getPayload(), $oSearchPayload);
    }
}

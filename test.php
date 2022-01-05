<?php

require_once 'vendor/autoload.php';

$oEnv = Dotenv\Dotenv::createImmutable(__DIR__);
$oEnv->safeLoad();

$oClient = new \Townnews\BLOX\Webservice\Client([
    'hostname' => $_ENV['BLOX_API_HOST'],
    'api_key' => $_ENV['BLOX_API_KEY'],
    'api_secret' => $_ENV['BLOX_API_SECRET'],
    'guzzle' => [
        'verify' => false
    ]
]);

print_r($oClient->get('editorial', 'search', ['q' => 'test']));
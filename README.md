## Installation
To add the BLOX webservice client to your project via `composer`:

```
composer require townnews/blox-webservice-client
```

## Configuring a client
Configure a client instance by providing the three required configuration
options:

* _hostname_: The BLOX host name that the client should process against
* _api\_key_: The API key provided in the BLOX admin for the given host
* _api\_secret_: The API secret provided in the BLOX admin for the given host

To create an instance:

```
<?php

require_once 'vendor/autoload.php';

$oClient = new \Townnews\BLOX\Webservice\Client([
    'hostname' => 'www.example.com',
    'api_key' => 'my-api-key',
    'api_secret' => 'my-api-secret'
]);
```

Internally, the class uses a Guzzle HTTP client. You can provide some options to
the internal client by passing the `guzzle` configuration parameter along with a
compatible array for the constructor of a `\GuzzleHttp\Client` instance.

## Make a HTTP GET API call
To make an API call using a GET call, you run:

```
$oResult = $oClient->get('editorial', 'search', [
    'q' => 'search terms'
]);
```

This will actually make a call to:

https://www.example.com/tncms/webservice/v1/editorial/search/?q=search+terms

A result object is returned and provides a way to get to an already parsed response payload by accessing:

```
$oParsed = $oResult->getPayload();
if ($oParsed->numResults) {
    foreach($oParsed->results as $oEntry) {
        echo $oEntry->title, \PHP_EOL;
    }
}
```

If desired, the raw `ResponseInterface` object can be retrieved with:

```
$oResp = $oResult->getResponse();
if ($oResp->getStatusCode() == 204) {
    // Do something special
}
```

If the API request resulted in an error, an exception will be thrown instead of a `Result` object being
returned.

## Make a HTTP POST API call
To make an API call with some files to upload as well:

```
$oResult = $oClient->post('editorial', 'create_asset', [
    'metadata' => json_encode([
        'type' => 'image',
        'title' => 'Example Image Asset',
        'id' => 'image-1'
    ]),
    'image' => '@/path/to/image.jpg'
]);
```

This will create a POST to the webservice end-point and upload an image file. Any
value that is prefixed with an `@` symbol will be resolved as a file.

Lastly, if no files are being posted, it is possible to make the body a JSON payload
with end-points that support this mechanism. This can be done by calling:

```
$oResult = $oClient->post('editorial', 'replace_asset', [
    'id' => 'article-1',
    'title' => 'Example Article Title',
    'content' => '<p>Paragraph 1</p>'
);
```
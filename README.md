
# PHP HTTP client

**Example**:

```php
<?php declare(strict_types=1);

include_once './vendor/autoload.php';

$client = new \Dewep\Client\Http();

$client->setUrl('https://localhost/test', ['a' => 1])
    ->setTimeout(10)
    ->sslVerify(true)
    ->setUserAgent('TestUserAgent');

$client->getHeaders()
    ->setContentType('text/json')
    ->setKeepAlive(10)
    ->setBasicAuth('login', 'pass');

$response = $client->post(
    [
        'test' => [
            'test'  => 'hello',
            'test2' => 'hello',
        ],
    ]
)->exec()->getResponse();

$info = $response->getInfo();
var_export($info);

$parsedResp = $response->getBody();
var_export($parsedResp);

$rawResp = $response->getBody(true);
var_export($rawResp);

$xml = $response->asXml(
    [
        'x' => 'http://xml.localhost/type/x',
    ]
);
var_export($xml);

```

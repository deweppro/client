<?php

include './Dewep/Funtion.php';
include './Dewep/Client/HttpInterface.php';
include './Dewep/Client/Http.php';


$client = new \Dewep\Client\Http();

$response = $client
        ->setUrl('https://packagist.org/search.json')
        ->setTimeout(120)
        ->methodGet(['q' => 'deweppro'])
        ->sslOff()
        ->make();

$bodySource = $response->getResponse();
$body = $response->getResponseJson();
$statusCode = $response->getStatusCode();
$head = $response->getResponseHead();
$info = $response->getResponseInfo();
$error = $response->getResponseError();

echo $client->getServerIp();

var_export([$bodySource, $body, $statusCode, $head, $info, $error]);
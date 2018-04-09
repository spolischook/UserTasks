<?php

require_once(__DIR__."/../vendor/autoload.php");

use GuzzleHttp\Psr7\ServerRequest;
use App\Kernel;

session_start();
$request = ServerRequest::fromGlobals();

$kernel = new Kernel('dev');
$response = $kernel->handle($request);

http_response_code($response->getStatusCode());
echo $response->getBody();

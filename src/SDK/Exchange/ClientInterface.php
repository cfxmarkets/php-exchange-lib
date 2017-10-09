<?php
namespace CFX\SDK\Exchange;

interface ClientInterface {
    public function __construct($baseUri, $apiKey, $secret, \GuzzleHttp\ClientInterface $httpClient);
    public function sendRequest($method, $uri, array $headers=[], $payload=null);
}



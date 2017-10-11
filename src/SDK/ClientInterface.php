<?php
namespace CFX\SDK;

interface ClientInterface {
    public function __construct($baseUri, $apiKey, $secret, \GuzzleHttp\ClientInterface $httpClient, FactoryInterface $f=null);
    public function sendRequest($method, $uri, array $params=[]);
    public function getFactory();
}



<?php
namespace CFX\SDK\Exchange;

class Client implements ClientInterface {
    protected $apiName = 'exchange';
    protected $apiVersion = '0';

    protected $baseUri;
    protected $apiKey;
    protected $apiKeySecret;
    protected $httpClient;

    // The actual clients doing the work
    protected $subclients = ['assets'];

    public function __construct($baseUri, $apiKey, $apiKeySecret, \GuzzleHttp\ClientInterface $httpClient) {
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
        $this->apiKeySecret = $apiKeySecret;
        $this->httpClient = $httpClient;

        $subclients = [];
        foreach($this->subclients as $c) $subclients[$c] = null;
        $this->subclients = $subclients;
    }

    public function __get($name) {
        if (!array_key_exists($name, $this->subclients)) throw new BadMethodCallException("Method `$name` does not exist on this object");
        if ($this->subclients[$name] === null) $this->subclients[$name] = $this->instantiateSubclient($name);
        return $this->subclients[$name];
    }

    protected function instantiateSubclient($name) {
        if ($name == 'assets') return new AssetsClient($this);

        throw new \CFX\SDK\Exchange\UnknownClientException("Don't know how to create clients of type `$name`");
    }

    public function sendRequest($method, $uri, array $headers=[], $payload=null) {
        $options = [
            'headers' => $headers,
            'body' => $payload,
        ];

        $r = $this->httpClient->createRequest($method, $uri, $options);
        $this->processResponse($this->httpClient->send($r));
        
    }

    protected function processResponse(\Psr7\Http\Message\ResponseInterface $r) {
        if ($r->getStatusCode() >= 500) throw new \RuntimeException("Server Error: ".$r->getBody());
        elseif ($r->getStatusCode() >= 400) throw new \RuntimeException("User Error: ".$r->getBody());
        elseif ($r->getStatusCode() >= 300) throw new \RuntimeException("Don't know how to handle 3xx codes.");
        elseif ($r->getStatusCode() >= 200) return $r;
        else throw new \RuntimeException("Don't know how to handle 1xx codes.");
    }
}


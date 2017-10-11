<?php
namespace CFX\SDK;

abstract class BaseClient implements ClientInterface {
    // Abstract properties to be overridden by children
    protected static $apiName;
    protected static $apiVersion;
    protected $subclients = [];

    // Instance properties
    protected $factory;
    protected $baseUri;
    protected $apiKey;
    protected $apiKeySecret;
    protected $httpClient;

    public function __construct($baseUri, $apiKey, $apiKeySecret, \GuzzleHttp\ClientInterface $httpClient, FactoryInterface $f=null) {
        if (!static::$apiName) throw new \RuntimeException("Programmer: You must define the \$apiName property for your Client.");
        if (static::$apiVersion === null) throw new \RuntimeException("Programmer: You must define the \$apiVersion property for your Client.");

        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
        $this->apiKeySecret = $apiKeySecret;
        $this->httpClient = $httpClient;

        if (!$f) $f = $this->createFactory();
        $this->factory = $f;

        $subclients = [];
        foreach($this->subclients as $c) $subclients[$c] = null;
        $this->subclients = $subclients;
    }

    public function __get($name) {
        if (!array_key_exists($name, $this->subclients)) throw new \BadMethodCallException("Method `$name` does not exist on this object");
        if ($this->subclients[$name] === null) $this->subclients[$name] = $this->instantiateSubclient($name);
        return $this->subclients[$name];
    }

    protected function instantiateSubclient($name) {
        throw new UnknownClientException("Don't know how to create clients of type `$name`");
    }

    public function sendRequest($method, $endpoint, array $params=[]) {
        // Composer URI
        $uri = $this->baseUri."/v".static::$apiVersion.$endpoint;

        // Add Authorization header if necessary

        if (!array_key_exists('headers', $params)) $params['headers'] = [];
        $authz_header = null;
        foreach($params['headers'] as $n => $v) {
            if (strtolower($n) == 'authorization') {
                $authz_header = $n;
                break;
            }
        }

        if (!$authz_header) $params['headers']['Authorization'] = "Basic ".base64_encode("$this->apiKey:$this->apiKeySecret");

        $r = $this->httpClient->createRequest($method, $uri, $params);
        return $this->processResponse($this->httpClient->send($r));
    }

    protected function processResponse($r) {
        if ($r->getStatusCode() >= 500) throw new \RuntimeException("Server Error: ".$r->getBody());
        elseif ($r->getStatusCode() >= 400) throw new \RuntimeException("User Error: ".$r->getBody());
        elseif ($r->getStatusCode() >= 300) throw new \RuntimeException("Don't know how to handle 3xx codes.");
        elseif ($r->getStatusCode() >= 200) return $r;
        else throw new \RuntimeException("Don't know how to handle 1xx codes.");
    }

    protected function createFactory() {
        return new Factory();
    }

    public function getFactory() {
        return $this->factory;
    }
}


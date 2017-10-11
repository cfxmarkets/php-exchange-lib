<?php
namespace CFX\SDK;

abstract class BaseSubclient implements SubclientInterface {
    protected static $resourceType;
    protected $cfxClient;

    public function __construct(ClientInterface $cfxClient) {
        if (static::$resourceType == null) throw new \RuntimeException("Programmer: You need to define this subclient's `\$resourceType` attribute. This should match the type of resources that this client deals in.");
        $this->cfxClient = $cfxClient;
    }

    public function create() {
        throw new \RuntimeException("Not yet implemented");
    }

    public function newCollection() {
        throw new \RuntimeException("Not yet implemented");
    }

    public function save(\KS\JsonApi\BaseResource $r) {
        throw new \RuntimeException("Not yet implemented");
    }

    public function get($q=null) {
        $endpoint = "/".static::$resourceType;
        if ($q) {
            if (substr($q, 0, 3) != 'id=' || strpos($q, ' ') !== false) throw new \RuntimeException("Programmer: for now, only id queries are accepted. Please pass `id=[asset-symbol]` if you'd like to query a specific asset. Otherwise, just get all assets and filter them yourself.");
            $isCollection = false;

            $endpoint .= "/".substr($q, 3);
        } else {
            $isCollection = true;
        }

        $r = $this->cfxClient->sendRequest('GET', $endpoint);
        $obj = json_decode($r->getBody(), true);

        return $this->inflateData($obj, $isCollection);
    }

    public function delete($r) {
        throw new \RuntimeException("Not yet implemented");
    }

    abstract protected function inflateData(array $obj, $isCollection);
}


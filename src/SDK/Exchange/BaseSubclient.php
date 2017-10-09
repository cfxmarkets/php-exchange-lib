<?php
namespace CFX\SDK\Exchange;

abstract class BaseSubclient implements SubclientInterface {
    protected $resourceType;
    protected $cfx;

    public function __construct(ClientInterface $cfx) {
        if ($this->resourceType == null) throw new \RuntimeException("Programmer: You need to define this subclient's `\$resourceType` attribute. This should match the type of resources that this client deals in.");
        $this->cfx = $cfx;
    }

    public function create() {
    }

    public function newCollection() {
    }

    public function save(\KS\JsonApi\BaseResource $r) {
    }

    public function get($q='') {
    }

    public function delete($r) {
    }
}


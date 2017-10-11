<?php
namespace CFX\SDK\Test;

class Client extends \CFX\SDK\BaseClient {
    protected static $apiName = 'tester';
    protected static $apiVersion = '1';
    protected $subclients = ['testers'];

    public function getSubclients() {
        return $this->subclients;
    }

    public function instantiateSubclient($name) {
        if ($name == 'testers') return new TestersClient($this);

        return parent::instantiateSubclient($name);
    }

    protected function createFactory() {
        return new \CFX\Test\Factory();
    }
}


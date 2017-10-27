<?php
namespace CFX\SDK\Exchange;

class Client extends \CFX\Persistence\Rest\AbstractDataContext implements ClientInterface {
    protected static $apiName = 'exchange';
    protected static $apiVersion = '0';

    protected function instantiateDatasource($name) {
        if ($name == 'assets') return new AssetsClient($this);

        throw new UnknownDatasourceException("Programmer: Don't know how to handle datasources of type `$name`. If you'd like to handle this, you should either add this datasource to the `instantiateClient` method in this class or create a derivative class and add it there.");
    }

    protected function composeUri($endpoint) {
        return $this->getBaseUri()."/v{$this->getApiVersion()}$endpoint";
    }
}


<?php
namespace CFX\SDK\Exchange;

abstract class AbstractDatasource extends \CFX\Persistence\Rest\AbstractDatasource {
    protected function composeUri($endpoint) {
        return $this->context->getBaseUri()."/v{$this->context->getApiVersion()}$endpoint";
    }
}


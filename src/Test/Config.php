<?php
namespace CFX\SDK\Exchange\Test;

class Config extends \KS\BaseConfig {
    public function getBaseExchangeUri() { return $this->get('exchange-base-uri'); }
    public function getExchangeApiKey() { return $this->get('exchange-api-key'); }
    public function getExchangeApiKeySecret() { return $this->get('exchange-api-key-secret'); }
}


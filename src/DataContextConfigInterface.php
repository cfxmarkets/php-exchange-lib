<?php
namespace CFX\SDK\Exchange;

interface DataContextConfigInterface extends \CFX\SDK\DataContextConfigInterface {
    public function getExchangeApiKey();
    public function getExchangeApiKeySecret();
}



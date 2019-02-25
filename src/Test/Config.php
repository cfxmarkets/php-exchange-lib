<?php
namespace CFX\SDK\Exchange\Test;

class Config extends \KS\WebappConfig {
    public function getBaseExchangeUri() { return $this->get('exchange-base-uri'); }
    public function getExchangeApiKey() { return $this->get('exchange-api-key'); }
    public function getExchangeApiKeySecret() { return $this->get('exchange-api-key-secret'); }
    protected function getValidExecProfiles(string $profileName = null)
    {
        if ($profileName) {
            if ($profileName === "test") {
                return "test";
            }
            parent::getValidExecProfiles($profileName);
        } else {
            return array_merge(parent::getValidExecProfiles(), [ "test" ]);
        }
    }
}


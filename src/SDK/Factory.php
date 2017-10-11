<?php
namespace CFX\SDK;

class Factory extends \CFX\Factory implements FactoryInterface {
    public function assetFromV1Data(array $data) {
        return \CFX\Exchange\Asset::fromV1($this, $data);
    }
}


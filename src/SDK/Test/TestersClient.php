<?php
namespace CFX\SDK\Test;

class TestersClient extends \CFX\SDK\BaseSubclient {
    protected static $resourceType = 'testers';

    protected function inflateData(array $data, $isCollection) {
        $f = $this->cfxClient->getFactory();

        if (!$isCollection) $data = [$data];
        foreach($data as $k => $o) $data[$k] = $f->newTester($o);
        return $isCollection ?
            $f->newJsonApiResourceCollection($data) :
            $data[0]
        ;
    }
}


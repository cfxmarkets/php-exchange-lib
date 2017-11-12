<?php
namespace CFX\SDK\Exchange;

class OrdersClient extends \CFX\Persistence\Rest\AbstractDatasource {
    protected $resourceType = 'orders';

    public function create(array $data=null, $type = null) {
        return new \CFX\Exchange\Order($this, $data);
    }

    public function get($q=null) {
        $opts = [];
        $endpoint = "/".$this->resourceType;
        $q = \CFX\Persistence\GenericDSLQuery::parse($q);
            $opts['query'] = ['orderKey' => $q->getId()];
        }

        $r = $this->sendRequest('GET', $endpoint, $opts);
        $obj = $this->convertV1Data(json_decode($r->getBody(), true), $q->requestingCollection());

        if (!$q->requestingCollection()) $obj = [$obj];
        $resource = $this->inflateData($obj, $q->requestingCollection());

        return $resource;
    }

    protected function convertV1Data($orders, $isCollection) {
        if (!$isCollection) $orders = [$orders];
        $data = [];
        foreach ($orders as $order) {
            // TODO: Get data specification for Order object and translate to json api format
            /*
            $data[] = [
                'id' => $order['order_id'],
                'type' => $this->resourceType,
                'attributes' => [
                    'type' => $order['order_type'],
                    'price' => ....
                ]
            ];
             */
            $data[] = $order;
        }

        return $isCollection ?
            $data :
            $data[0]
        ;
    }
}



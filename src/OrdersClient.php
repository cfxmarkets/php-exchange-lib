<?php
namespace CFX\SDK\Exchange;

class OrdersClient extends \CFX\Persistence\Rest\AbstractDatasource {
    protected $resourceType = 'orders';

    public static function mapStatus($from)
    {
        $status = [
            '0' => 'new',
            '1' => 'active',
            '2' => 'matched',
            '-1' => 'cancelled',
            '-2' => 'cancelled',
        ];

        if (array_key_exists($from, $status)) {
            return $status[$from];
        } else {
            $status = array_search($from, $status);
            if ($status === false) {
                return null;
            } else {
                return $status;
            }
        }
    }

    public function getClassMap()
    {
        return [
            'private' => "\\CFX\\Exchange\\Order",
            'public' => "\\CFX\\Exchange\\Order",
        ];
    }

    public function get($q=null) {
        $opts = [ 'query' => []];
        $endpoint = "/".$this->resourceType;
        $q = $this->parseDSL($q);
        if ($q->getId()) {
            $opts['query']['orderKey'] = $q->getId();
        }
        if ($q->getAccountKey()) {
            $opts['query']['accountKey'] = $q->getAccountKey();
        }
        if (count($opts['query']) === 0) {
            unset($opts['query']);
        }

        $r = $this->sendRequest('GET', $endpoint, $opts);
        $obj = $this->convertFromV1Data(json_decode($r->getBody(), true), $q->requestingCollection());

        if (!$q->requestingCollection()) $obj = [$obj];
        $resource = $this->inflateData($obj, $q->requestingCollection());

        return $resource;
    }

    /**
     * Overridden because the exchange server doesn't transact in jsonapi
     *
     * {@inheritdoc}
     */
    protected function _saveRest($method, $endpoint, \CFX\JsonApi\ResourceInterface $r, $justChanges = false) {
        $body = $this->convertToV1Data($r, $justChanges);

        if ($method === 'PATCH') {
            $method = 'PUT';
            $endpoint = '/orders';
            $body['orderKey'] = $r->getId();
        }

        $response = $this->sendRequest($method, $endpoint, [ 'body' => $body ]);

        // This response does not contain the new Order data in it. Only refreshing ID.
        $data = json_decode($response->getBody(), true);
        if (!$data) {
            $msg = "Uh oh! The CFX Api Server seems to have screwed up. It didn't return valid json data.";
            if ($this->debug) {
                $msg .= " Here's what it returned:\n\n".$response->getBody();
            }
            throw new \RuntimeException($msg);
        }
        if (array_key_exists('orderKey', $data)) {
            $this->currentData = [
                'id' => $data['orderKey']
            ];
            $r->restoreFromData();
        }

        return $this;
    }

    protected function convertFromV1Data($orders, $isCollection) {
        if (!$isCollection) $orders = [$orders];
        $data = [];
        foreach ($orders as $order) {
            /*
            {
              "orderKey": "f539f08acb3f9807e00dd367851de1e9",
              "assetSymbol": "FR008",
              "orderType": "buy",
              "orderTime": "2017-09-22 13:45:49",
              "orderPrice": "1",
              "orderQuantity": "100",
              "orderFee": "2.5",
              "orderStatus": "-1",
              "accountKey": "a9e1a313e686e159849bd1e0d3e04dc9",
              "documentKey": null,
              "documentUrl": null,
              "documentTime": null,
              "vaultKey": "undefined",
              "activities": [
                
              ]
            }

            1 = active
            2 = matched
           -1 = cancelled
           -2 = reversed
             */

            $data[] = [
                'id' => $order['orderKey'],
                'type' => $this->resourceType,
                'attributes' => [
                    'side' => $order['orderType'],
                    'lotSize' => $order['orderQuantity'],
                    'currentPrice' => $order['orderPrice'],
                    'status' => $this::mapStatus($order['orderStatus']),
                    'documentKey' => $order['documentKey'],
                    'referenceKey' => $order['accountKey'],
                    'bankAccountId' => ($order['vaultKey'] === "undefined" ? null : $order['vaultKey']),
                ],
                'relationships' => [
                    'asset' => [
                        'data' => [
                            'id' => $order['assetSymbol'],
                            'type' => 'assets',
                        ]
                    ]
                ]
            ];
        }

        return $isCollection ?
            $data :
            $data[0]
        ;
    }

    protected function convertToV1Data(\CFX\JsonApi\DataInterface $resource, $onlyChanges = false)
    {
        if ($resource instanceof \CFX\JsonApi\ResourceInterface) {
            $resource = [ $resource ];
        }

        $data = [];

        foreach ($resource as $r) {
            $d = [];
            if ($onlyChanges) {
                $changes = $r->getChanges();
            } else {
                $changes = $r->jsonSerialize();
            }

            if ($r->getSide() === 'buy' && $r->getPriceLow() !== null) {
                if (array_key_exists('priceLow', $changes['attributes'])) {
                    $d['orderPrice'] = $changes['attributes']['priceLow'];
                }
                if (array_key_exists('priceHigh', $changes['attributes'])) {
                    $d['orderPriceUpper'] = $changes['attributes']['priceHigh'];
                }
            } else {
                if (array_key_exists('priceHigh', $changes['attributes'])) {
                    $d['orderPrice'] = $changes['attributes']['priceHigh'];
                }
                if (array_key_exists('priceLow', $changes['attributes'])) {
                    $d['orderPriceLower'] = $changes['attributes']['priceLow'];
                }
            }

            if (array_key_exists('referenceKey', $changes['attributes'])) {
                $d['accountKey'] = $changes['attributes']['referenceKey'];
                $d['referenceKey'] = $changes['attributes']['referenceKey'];
            }

            foreach($changes['attributes'] as $field => $v) {
                $mapped = $this->mapField($field);
                if ($mapped) {
                    $d[$mapped] = $this->mapValue($mapped, $v);
                }
            }
            if (array_key_exists('relationships', $changes)) {
                foreach($changes['relationships'] as $field => $v) {
                    $mapped = $this->mapField($field);
                    if ($mapped) {
                        $d[$mapped] = $v->getData();
                        if ($d[$mapped]) {
                            $d[$mapped] = $d[$mapped]->getId();
                        }
                    }
                }
            }

            $data[] = $d;
        }

        if ($resource instanceof \CFX\JsonApi\ResourceCollectionInterface) {
            return $data;
        } else {
            return $data[0];
        }
    }

    public function mapValue($field, $val)
    {
        if ($field === 'orderStatus' || $field === 'status') {
            return $this::mapStatus($val);
        } else {
            return $val;
        }
    }

    public function mapField($field)
    {
        $map = [
            'assetSymbol' => 'asset',
            'orderType' => 'side',
            'orderQuantity' => 'lotSize',
            'documentKey' => 'documentKey',
            'vaultKey' => 'bankAccountId',
        ];

        if (array_key_exists($field, $map)) {
            return $map[$field];
        } else {
            $mapped = array_search($field, $map);
            if ($mapped === false) {
                return null;
            } else {
                return $mapped;
            }
        }
    }

    public function delete($r)
    {
        if ($r instanceof \CFX\JsonApi\ResourceInterface) {
            $r = $r->getId();
        }

        // If we're trying to delete a resource that was never saved, just return
        if (!$r) {
            return $this;
        }

        $this->sendRequest('DELETE', "/$this->resourceType", [
            'body' => [
                'orderKey' => $r
            ]
        ]);
    }

    protected function parseDSL($q)
    {
        return OrdersDSLQuery::parse($q);
    }
}



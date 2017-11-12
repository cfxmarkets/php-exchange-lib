<?php
namespace CFX\SDK\Exchange;

class AssetsClient extends \CFX\Persistence\Rest\AbstractDatasource {
    protected $resourceType = 'assets';

    public function create(array $data=null, $type = null) {
        return new \CFX\Exchange\Asset($this, $data);
    }

    public function get($q=null) {
        $opts = [];
        $endpoint = "/".$this->resourceType;
        if ($q) {
            if (substr($q, 0, 3) != 'id=' || strpos($q, ' ') !== false) throw new \RuntimeException("Programmer: for now, only id queries are accepted. Please pass `id=[asset-symbol]` if you'd like to query a specific asset. Otherwise, just get all assets and filter them yourself.");
            $isCollection = false;
            $opts['query'] = ['symbol' => substr($q, 3)];
        } else {
            $isCollection = true;
        }

        $r = $this->sendRequest('GET', $endpoint, $opts);
        $obj = $this->convertV1Data(json_decode($r->getBody(), true), $isCollection);

        if (!$isCollection) $obj = [$obj];
        $resource = $this->inflateData($obj, $isCollection);

        return $resource;
    }

    protected function convertV1Data($assets, $isCollection) {
        if (!$isCollection) $assets = [$assets];
        $data = [];
        foreach ($assets as $asset) {
            $data[] = [
                'id' => $asset['asset_symbol'],
                'type' => $this->resourceType,
                'attributes' => [
                    'issuer' => $asset['issuer_ident'],
                    'name' => $asset['asset_name'],
                    'type' => $asset['asset_type'],
                    'statusCode' => $asset['asset_status'],
                    'statusText' => $asset['asset_status_text'],
                    'description' => $asset['asset_description'],
                ]
            ];
        }

        return $isCollection ?
            $data :
            $data[0]
        ;
    }
}


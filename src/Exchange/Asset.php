<?php
namespace CFX\Exchange;

class Asset extends \KS\JsonApi\BaseResource implements AssetInterface {
    protected $resourceType = 'assets';
    protected $attributes = [
        'issuer' => null,
        'name' => null,
        'type' => null,
        'statusCode' => null,
        'statusText' => null,
        'description' => null
    ];
    protected $relationships = [];

    public static function fromV1(\KS\JsonApi\FactoryInterface $f, $asset) {
        $data = [
            'id' => $asset['asset_symbol'],
            'attributes' => [
                'issuer' => $asset['issuer_ident'],
                'name' => $asset['asset_name'],
                'type' => $asset['asset_type'],
                'statusCode' => $asset['asset_status'],
                'statusText' => $asset['asset_status_text'],
                'description' => $asset['asset_description'],
            ]
        ];
        return new static($f, $data);
    }

    public function getIssuer() { return $this->attributes['issuer']; }
    public function getName() { return $this->attributes['name']; }
    public function getType() { return $this->attributes['type']; }
    public function getStatusCode() { return $this->attributes['statusCode']; }
    public function getStatusText() { return $this->attributes['statusText']; }
    public function getDescription() { return $this->attributes['description']; }

    public function setIssuer($val) {
        $this->attributes['issuer'] = $val;
        return $this;
    }
    public function setName($val) {
        $this->attributes['name'] = $val;
        return $this;
    }
    public function setType($val) {
        $this->attributes['type'] = $val;
        return $this;
    }
    public function setStatusCode($val) {
        $this->attributes['statusCode'] = $val;
        return $this;
    }
    public function setStatusText($val) {
        $this->attributes['statusText'] = $val;
        return $this;
    }
    public function setDescription($val) {
        $this->attributes['description'] = $val;
        return $this;
    }






    // Interface implementations

    public function fetch(DatasourceInterface $db) {
        throw new UnimplementedFeatureException("`fetch` is not yet implemented for Assets.");
    }
}


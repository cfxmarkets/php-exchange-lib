<?php
namespace CFX\SDK\Exchange;

class OrdersDSLQuery extends \CFX\Persistence\GenericDSLQuery
{
    protected $primaryKey = 'orderKey';

    protected static function getAcceptableFields()
    {
        return array_merge(parent::getAcceptableFields(), [ 'accountKey' ]);
    }

    public function setAccountKey($operator, $val)
    {
        return $this->setExpressionValue('accountKey', [
            "field" => "accountKey",
            "operator" => $operator,
            "value" => $val,
        ]);
    }

    public function unsetAccountKey()
    {
        return $this->setExpressionValue('accountKey', null);
    }

    public function getAccountKey()
    {
        return $this->getExpressionValue('accountKey');
    }
}


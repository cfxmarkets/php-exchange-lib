<?php
namespace CFX\SDK\Exchange;

class FundsTransfersDSLQuery extends \CFX\Persistence\GenericDSLQuery
{
    protected static function getAcceptableFields()
    {
        return [ 'legalEntityId', "fundingSourceId" ];
    }

    public function setLegalEntityId($operator, $val)
    {
        return $this->setExpressionValue('legalEntityId', [
            "field" => "legalEntityId",
            "operator" => $operator,
            "value" => $val,
        ]);
    }

    public function unsetLegalEntityId()
    {
        return $this->setExpressionValue('legalEntityId', null);
    }

    public function getLegalEntityId()
    {
        return $this->getExpressionValue('legalEntityId');
    }



    public function setFundingSourceId($operator, $val)
    {
        return $this->setExpressionValue('fundingSourceId', [
            "field" => "fundingSourceId",
            "operator" => $operator,
            "value" => $val,
        ]);
    }

    public function unsetFundingSourceId()
    {
        return $this->setExpressionValue('fundingSourceId', null);
    }

    public function getFundingSourceId()
    {
        return $this->getExpressionValue('fundingSourceId');
    }
}



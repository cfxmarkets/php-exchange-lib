<?php
namespace CFX\SDK\Exchange;

class FundsTransfersClient extends \CFX\Persistence\Rest\AbstractDatasource {
    protected $resourceType = 'funds-transfers';

    public static function mapStatus($from)
    {
        $status = [
            '0', //=> 'new',
            '1', //=> 'received',
            '2', //=> 'synapse-received',
            '3', //=> 'settled'
            '-1', //=> 'rejected',
            '-2', //=> 'synapse-rejected',
            '-3', //=> 'rolled-back',
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
            'private' => "\\CFX\\Brokerage\\FundsTransfer",
            'public' => "\\CFX\\Brokerage\\FundsTransfer",
        ];
    }

    /**
     * Must use account_key (LegalEntityId) to get all transfers
     * Can optionally use fundingSourceId to filter transfers by specific source
     * For now, can't get by transferID
     */
    public function get($q=null, string $sort = null, ?array $pagination = null)
    {
        $opts = [ 'query' => []];
        $endpoint = "/funding/transfers";
        $q = $this->parseDSL($q);

        if ($q->getId()) {
            throw new \CFX\Persistence\BadQueryException("Can't get funds transfers by id");
        }
        if (!$q->getLegalEntityId()) {
            throw new \CFX\Persistence\BadQueryException("You must specify a LegalEntityId in your query");
        }

        $opts['query']['account_key'] = $q->getLegalEntityId();

        if ($q->getFundingSourceId()){
            $opts["query"]["funding_source_key"] = $q->getFundingSourceId();
        }

        $r = $this->sendRequest('GET', $endpoint, $opts);
        $data = (string)$r->getBody();
        if ($data === "") {
            $data = "[]";
        }
        $data = json_decode($data, true);
        if ($data === null) {
            $msg = "Uh oh! The CFX Api Server seems to have screwed up. It didn't return valid json data.";
            if ($this->debug) {
                $msg .= " Here's what it returned:\n\n".$r->getBody();
            }
            throw new \RuntimeException($msg);
        }
        $obj = $this->convertFromV1Data($data, $q->requestingCollection());

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

        $endpoint = "/funding/transfers";

        $response = $this->sendRequest($method, $endpoint, [ 'body' => $body ]);

        // This response does not contain the new transfer data in it. Only refreshing ID.
        $data = json_decode($response->getBody(), true);
        if (!$data) {
            $msg = "Uh oh! The CFX Api Server seems to have screwed up. It didn't return valid json data.";
            if ($this->debug) {
                $msg .= " Here's what it returned:\n\n".$response->getBody();
            }
            throw new \RuntimeException($msg);
        }
        if (array_key_exists("transfer", $data)) {
            $this->currentData = $this->convertFromV1Data($data["transfer"], false);
            $r->restoreFromData();
        } elseif (array_key_exists('transfer_key', $data)) {
            $this->currentData = [
                'id' => $data['transfer_key']
            ];
            $r->restoreFromData();
        }

        return $this;
    }

    protected function convertFromV1Data($v1Data, $isCollection) {
        if (!$isCollection) $v1Data = [$v1Data];
        $data = [];
        foreach ($v1Data as $row) {
            /*
              {
                "transfer_key": "3b1c6fd28d1cf269b821d5cae4c3c589",
                "transfer_type": "withdraw",
                "transfer_amount": "100",
                "transfer_memo": "Withdraw from Deposit Account",
                "transfer_time": "2016-02-15 12:20:22",
                "transfer_status": "2",
                "transfer_status_text": null,
                "funding_source_key": "0d688b1cfb1968cebd1810d3b10bf52f",
                "target_funding_source_key": "015a267d20da94664a332232f6b62ff4",
                "funding_payment_key": "08ff7c546675bb0862ba3f37d5195702",
                "funding_payment_status": "2",
                "v2legalEntityId": "718e4867-7f44-11e4-8821-003048d9078a",
                "v2type": "debit",
                "v2fundingSource": "015a267d20da94664a332232f6b62ff4"
              }

            1 = received
            2 = received-upstream
            3 = complete
           -1 = rejected
           -2 = rejected-upstream
           -3 = rolled-back
             */

            // If v2 data is incomplete, skip it
            if (!$row["v2type"] || !$row["v2fundingSource"]) {
                continue;
            }

            $data[] = [
                'id' => $row["transfer_key"],
                'type' => $this->resourceType,
                'attributes' => [
                    "type" => $row["v2type"],
                    "amount" => $row["transfer_amount"],
                    "status" => $row["transfer_status"],
                    "createdOn" => $row["transfer_time"],
                    "idpKey" => $row["reference_key"] ?? null,
                    "memo" => $row["transfer_memo"],
                ],
                'relationships' => [
                    "fundingSource" => [
                        "data" => [
                            "id" => $row["v2fundingSource"],
                            "type" => "funding-sources",
                        ]
                    ],
                    "legalEntity" => [
                        "data" => [
                            "id" => $row["v2legalEntityId"],
                            "type" => "legal-entities",
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

            $d["account_key"] = $r->getLegalEntity()->getId();
            $d["target_account_key"] = $d["account_key"];
            $d["transfer_type"] = $r->getType() === "debit" ? "withdraw" : "deposit";
            $d["transfer_amount"] = $r->getAmount();
            $d["funding_source_key"] = $r->getType() === "debit" ?  null : $r->getFundingSource()->getId();
            $d["target_funding_source_key"] = $r->getType() === "debit" ?  $r->getFundingSource()->getId() : null;
            $d["reference_key"] = $r->getIdpKey();
            $d["transfer_memo"] = $r->getMemo();

            $data[] = $d;
        }

        if ($resource instanceof \CFX\JsonApi\ResourceCollectionInterface) {
            return $data;
        } else {
            return $data[0];
        }
    }

    protected function parseDSL($q)
    {
        return \CFX\Brokerage\FundsTransfersDSLQuery::parse($q);
    }
}




<?php
namespace CFX\SDK\Exchange;

class FundsTransfersClientTest extends \PHPUnit\Framework\TestCase
{
    protected static $httpClient;
    protected static $cfx;
    protected static $emptyResponse;
    protected static $collectionResponse;
    protected static $errorResponse;
    protected static $postSuccessResponse;
    protected static $fundsTransferV1Data = [
		"transfer_key" => "cf68c1e78a9ccf621a770b0e7ee89365",
		"transfer_type" => "withdraw",
		"transfer_amount" => "1000",
		"transfer_memo" => "Withdraw from Deposit Account",
		"transfer_time" => "2016-02-15 12:08:41",
		"transfer_status" => "2",
		"transfer_status_text" => null,
		"funding_source_key" => "0d688b1cfb1968cebd1810d3b10bf52f",
		"target_funding_source_key" => "015a267d20da94664a332232f6b62ff4",
		"funding_payment_key" => "2a71d304d6c7c0a114dcd13c493489d1",
		"funding_payment_status" => "2",
		"v2legalEntityId" => "718e4867-7f44-11e4-8821-003048d9078a",
		"v2type" => "debit",
		"v2fundingSource" => "015a267d20da94664a332232f6b62ff4",
    ];
    protected static $fundsTransferV2Data = [
        "type" => "funds-transfers",
        "attributes" => [
            "type" => "debit",
            "amount" => 54321,
            "idpKey" => "aaaabbbbccccddddeeee",
        ],
        "relationships" => [
            "legalEntity" => [
                "data" => [
                    "id" => "1111222233334444aaaabbbbccccdddd",
                    "type" => "legal-entities",
                ],
            ],
            "fundingSource" => [
                "data" => [
                    "id" => "ddddccccbbbbaaaa4444333322221111",
                    "type" => "funding-sources",
                ],
            ],
        ],
    ];

    public static function setUpBeforeClass()
    {
        static::$httpClient = new \CFX\Persistence\Test\HttpClient();
        static::$cfx = new \CFX\SDK\Exchange\Client('https://null.cfxtrading.com', '12345', 'abcde', static::$httpClient);
        static::$emptyResponse = new \GuzzleHttp\Psr7\Response(200, [ "Content-Type" => "application/json" ], \GuzzleHttp\Psr7\stream_for("{}"));
        static::$collectionResponse = new \GuzzleHttp\Psr7\Response(200, [ "Content-Type" => "application/json" ], \GuzzleHttp\Psr7\stream_for(json_encode([self::$fundsTransferV1Data])));
        static::$errorResponse = new \GuzzleHttp\Psr7\Response(400, [ "Content-Type" => "application/json" ], \GuzzleHttp\Psr7\stream_for('{"status":"error","message":"There was a problem."}'));
        static::$postSuccessResponse = new \GuzzleHttp\Psr7\Response(201, [ "Content-Type" => "application/json" ], \GuzzleHttp\Psr7\stream_for('{"status":"success","message":"Your transfer was successfully submitted....","transfer_key":"11223344","funding_payment_key":"44332211"}'));
    }

    public function testThrowsExceptionsOnBadQueries()
    {
        try {
            static::$httpClient->setNextResponse(static::$emptyResponse);
            static::$cfx->fundsTransfers->get();
            $this->fail("Should have thrown an exception for query without parameters");
        } catch (\CFX\Persistence\BadQueryException $e) {
            $this->assertContains("must specify", $e->getMessage());
        }

        try {
            static::$httpClient->setNextResponse(static::$emptyResponse);
            static::$cfx->fundsTransfers->get("id=abcde12345");
            $this->fail("Should have thrown an exception for query with ID");
        } catch (\CFX\Persistence\BadQueryException $e) {
            $this->assertContains("unacceptable fields or values found", strtolower($e->getMessage()));
            $this->assertContains("offending expression: `id=abcde12345`", strtolower($e->getMessage()));
        }

        try {
            static::$httpClient->setNextResponse(static::$emptyResponse);
            static::$cfx->fundsTransfers->get("fundingSourceId=abcde12345");
            $this->fail("Should have thrown an exception for query with only funding source");
        } catch (\CFX\Persistence\BadQueryException $e) {
            $this->assertContains("you must specify a legalentityid in your query", strtolower($e->getMessage()));
        }
    }

    public function testAcceptsGoodQueries()
    {
        static::$httpClient->setNextResponse(static::$emptyResponse);
        static::$cfx->fundsTransfers->get("legalEntityId=abcde12345 and fundingSourceId=abcde12345");
        $this->assertTrue(true, "Good. Didn't throw an Exception");

        static::$httpClient->setNextResponse(static::$emptyResponse);
        static::$cfx->fundsTransfers->get("legalEntityId=abcde12345");
        $this->assertTrue(true, "Good. Didn't throw an Exception");
    }

    public function testComposesUriCorrectly() {
        static::$httpClient->setNextResponse(static::$emptyResponse);
        $transfers = static::$cfx->fundsTransfers->get("legalEntityId=12345");
        $req = static::$httpClient->getLastRequest();
        $this->assertEquals('https://null.cfxtrading.com/v0/funding/transfers?account_key=12345', (string)$req->getUri());

        static::$httpClient->setNextResponse(static::$emptyResponse);
        $transfers = static::$cfx->fundsTransfers->get('legalEntityId=12345 and fundingSourceId=abcde');
        $req = static::$httpClient->getLastRequest();
        $this->assertEquals('https://null.cfxtrading.com/v0/funding/transfers?account_key=12345&funding_source_key=abcde', (string)$req->getUri());

        static::$httpClient->setNextResponse(static::$postSuccessResponse);
        $transfer = static::$cfx->fundsTransfers->create(static::$fundsTransferV2Data);
        $response = static::$cfx->fundsTransfers->save($transfer);
        $req = static::$httpClient->getLastRequest();
        $this->assertEquals('https://null.cfxtrading.com/v0/funding/transfers', (string)$req->getUri());
    }

    public function testSuccessfullyConvertsFromV1OnReceive()
    {
        static::$httpClient->setNextResponse(static::$collectionResponse);
        $transfers = static::$cfx->fundsTransfers->get("legalEntityId=12345");
        $this->assertInstanceOf("\\CFX\\JsonApi\\ResourceCollectionInterface", $transfers);
        $this->assertEquals(1, count($transfers));
        $this->assertInstanceOf("\\CFX\\Brokerage\\FundsTransfer", $transfers[0]);
    }

    public function testSuccessfullyConvertsToV1Onsend()
    {
        static::$httpClient->setNextResponse(static::$postSuccessResponse);
        $transfer = static::$cfx->fundsTransfers->create(static::$fundsTransferV2Data);
        $response = static::$cfx->fundsTransfers->save($transfer);
        $req = static::$httpClient->getLastRequest();

        $expected = [];
        $expected["account_key"] = $transfer->getLegalEntity()->getId();
        $expected["target_account_key"] = $expected["account_key"];
        $expected["transfer_type"] = $transfer->getType() === "debit" ? "withdraw" : "deposit";
        $expected["transfer_amount"] = $transfer->getAmount();
        $expected["funding_source_key"] = $transfer->getType() === "debit" ?  null : $transfer->getFundingSource()->getId();
        $expected["target_funding_source_key"] = $transfer->getType() === "debit" ?  $transfer->getFundingSource()->getId() : null;
        $expected["referenceKey"] = $transfer->getIdpKey();

        $expected = http_build_query($expected);
        $this->assertEquals($expected, (string)$req->getBody());
    }
}


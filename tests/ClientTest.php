<?php

class ClientTest extends \PHPUnit\Framework\TestCase {
    public function testInstantiates() {
        $cfx = new \CFX\SDK\Exchange\Client('https://null.cfxtrading.com', '12345', 'abcde', new \GuzzleHttp\Client());
        $this->assertInstanceOf("\\CFX\\SDK\\Exchange\\ClientInterface", $cfx);
    }




    // API Protocol Negotiations


    public function testClientComposesUriCorrectly() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Exchange\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(200));
        $cfx->send('GET', '/assets');

        $r = $httpClient->getLastRequest();
        $this->assertEquals('https://null.cfxtrading.com/v0/assets', (string)$r->getUri());
    }

    public function testClientSetsAuthorizationHeadersCorrectly() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Exchange\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(200));
        $cfx->send('GET', '/assets');

        $r = $httpClient->getLastRequest();
        $this->assertEquals('Basic '.base64_encode('12345:abcde'), $r->getHeader('Authorization'));
    }

    public function testClientThrowsExceptionsOnNon200Responses() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Exchange\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(599));
        try {
            $cfx->send('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('server error', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(500));
        try {
            $cfx->send('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('server error', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(499));
        try {
            $cfx->send('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('user error', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(400));
        try {
            $cfx->send('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('user error', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(399));
        try {
            $cfx->send('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('3xx', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(300));
        try {
            $cfx->send('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('3xx', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(199));
        try {
            $cfx->send('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('1xx', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(100));
        try {
            $cfx->send('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('1xx', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(299));
        $r = $cfx->send('GET', '/assets');
        $this->assertEquals(299, $r->getStatusCode());

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(200));
        $r = $cfx->send('GET', '/assets');
        $this->assertEquals(200, $r->getStatusCode());
    }





    // General subclient tests


    public function testCanGetAssetsClient() {
        $cfx = new \CFX\SDK\Exchange\Client('https://null.cfxtrading.com', '12345', 'abcde', new \GuzzleHttp\Client());
        $assetsClient = $cfx->assets;
        $this->assertInstanceOf('\\CFX\\SDK\\Exchange\AssetsClient', $assetsClient);
    }

    public function testAssetsClientCanGetAllAssets() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Exchange\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(200, ['Content-Type' => 'application/json'], \GuzzleHttp\Stream\Stream::factory(json_encode([
            [
                "asset_id" => "36",
                "issuer_ident" => "TEMP",
                "account_key" => "76b7137d-5555-11e4-8141-003048d9078a",
                "asset_symbol" => "FR008",
                "asset_type" => "realestate",
                "offer_type" => "exchange",
                "finance_type" => "equity",
                "exemption_type" => "506c",
                "asset_name" => "141 South Meridian Street",
                "asset_description" => "Test Description",
                "offer_amount" => "250000",
                "max_amount" => "250000",
                "min_amount" => "250000",
                "share_price_initial" => "5000",
                "open_date" => "2005-01-01 00:00:00",
                "close_date" => "2005-01-01 00:00:00",
                "asset_status" => "1",
                "asset_status_text" => "open",
                "amount_reserved" => "0",
                "amount_investors" => "0",
            ]
        ]))));

        $assets = $cfx->assets->get();
        $this->assertEquals(1, count($assets));
        $this->assertContains('asset_id', array_keys($assets[0]));
    }



    // V2 tests
    /*

    public function testComposesUriCorrectly() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Exchange\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);
        $httpClient->setNextResponse(new \GuzzleHttp\Response(200));
        $cfx->sendRequest('GET', '/assets');
        $this->assertEquals('https://null.cfxtrading.com/exchange/v2/assets', (string)$httpClient->getLastRequest->getUri());
    }

    public function testSetsAuthorizationHeadersCorrectly() {
        $this->markTestIncomplete();
    }

    public function testCanSetApiVersionThroughClient() {
        $this->markTestIncomplete();
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Exchange\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);
        $cfx->setApiVersion('1');
        $cfx->createRequest('GET', '/assets');
    }
     */
}


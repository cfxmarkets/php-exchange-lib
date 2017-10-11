<?php

class BaseClientTest extends \PHPUnit\Framework\TestCase {
    public function testInstantiates() {
        $cfx = new \CFX\SDK\Test\Client('https://null.cfxtrading.com', '12345', 'abcde', new \GuzzleHttp\Client());
        $this->assertInstanceOf("\\CFX\\SDK\\ClientInterface", $cfx);
    }




    // API Protocol Negotiations


    public function testClientComposesUriCorrectly() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Test\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(200));
        $cfx->sendRequest('GET', '/assets');

        $r = $httpClient->getLastRequest();
        $this->assertEquals('https://null.cfxtrading.com/v1/assets', $r->getUrl());
    }

    public function testClientSetsAuthorizationHeadersCorrectly() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Test\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(200));
        $cfx->sendRequest('GET', '/assets');

        $r = $httpClient->getLastRequest();
        $this->assertEquals('Basic '.base64_encode('12345:abcde'), $r->getHeader('Authorization'));
    }

    public function testClientThrowsExceptionsOnNon200Responses() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Test\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(599));
        try {
            $cfx->sendRequest('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('server error', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(500));
        try {
            $cfx->sendRequest('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('server error', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(499));
        try {
            $cfx->sendRequest('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('user error', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(400));
        try {
            $cfx->sendRequest('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('user error', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(399));
        try {
            $cfx->sendRequest('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('3xx', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(300));
        try {
            $cfx->sendRequest('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('3xx', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(199));
        try {
            $cfx->sendRequest('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('1xx', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(100));
        try {
            $cfx->sendRequest('GET', '/assets');
            $this->fail("Should have thrown exception");
        } catch (\RuntimeException $e) {
            $this->assertContains('1xx', strtolower($e->getMessage()));
        }

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(299));
        $r = $cfx->sendRequest('GET', '/assets');
        $this->assertEquals(299, $r->getStatusCode());

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(200));
        $r = $cfx->sendRequest('GET', '/assets');
        $this->assertEquals(200, $r->getStatusCode());
    }




    // Subclients

    public function testCanGetSubclient() {
        $cfx = new \CFX\SDK\Test\Client('https://null.cfxtrading.com', '12345', 'abcde', new \GuzzleHttp\Client());
        $subClient = $cfx->testers;
        $this->assertInstanceOf('\\CFX\\SDK\\SubclientInterface', $subClient);
    }

    public function testInstantiatesSubclientOnDemand() {
        $cfx = new \CFX\SDK\Test\Client('https://null.cfxtrading.com', '12345', 'abcde', new \GuzzleHttp\Client());
        $this->assertNull($cfx->getSubclients()['testers']);
        $cfx->testers;
        $this->assertInstanceOf("\\CFX\\SDK\\SubclientInterface", $cfx->getSubclients()['testers']);
    }






    // V2 tests
    /*

    public function testComposesUriCorrectly() {
        $this->markTestIncomplete();
    }

    public function testSetsAuthorizationHeadersCorrectly() {
        $this->markTestIncomplete();
    }

    public function testCanSetApiVersionThroughClient() {
    }
     */
}


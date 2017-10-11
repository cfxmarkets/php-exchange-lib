<?php

class BaseSubclientTest extends \PHPUnit\Framework\TestCase {
    protected static $testers = [
        [
            'id' => '1',
            'type' => 'testers',
            'attributes' => [
                'name' => 'Jim Chavo',
                'dob' => 1234567890,
                'exists' => 1,
                'active' => 1,
            ],
            'relationships' => [
                'friends' => [
                    'data' => [[ 'id' => '2', 'type' => 'testers' ]],
                ],
                'bestFriend' => [
                    'data' => [ 'id' => '2', 'type' => 'testers' ],
                ],
            ],
        ],
        [
            'id' => '2',
            'type' => 'testers',
            'attributes' => [
                'name' => 'Jane Chavo',
                'dob' => 123454321,
                'exists' => 1,
                'active' => 1,
            ],
            'relationships' => [
                'friends' => [
                    'data' => [[ 'id' => '1', 'type' => 'testers' ]],
                ],
                'bestFriend' => [
                    'data' => [ 'id' => '1', 'type' => 'testers' ],
                ],
            ],
        ],
    ];


    public function testSubclientClientComposesUriCorrectly() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Test\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(
            200,
            ['Content-Type' => 'application/json'],
            \GuzzleHttp\Stream\Stream::factory(json_encode(self::$testers))
        ));
        $testers = $cfx->testers->get();
        $r = $httpClient->getLastRequest();
        $this->assertEquals('https://null.cfxtrading.com/v1/testers', $r->getUrl());

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(
            200,
            ['Content-Type' => 'application/json'],
            \GuzzleHttp\Stream\Stream::factory(json_encode(self::$testers[0]))
        ));
        $testers = $cfx->testers->get('id=1');
        $r = $httpClient->getLastRequest();
        $this->assertEquals('https://null.cfxtrading.com/v1/testers/1', $r->getUrl());
    }

    public function testSubclientClientCanGetAllTesterss() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Test\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(
            200,
            ['Content-Type' => 'application/json'],
            \GuzzleHttp\Stream\Stream::factory(json_encode(self::$testers))
        ));

        $testers = $cfx->testers->get();
        $this->assertEquals(2, count($testers));
        $this->assertInstanceOf("\\CFX\\Test\\TesterInterface", $testers[0]);
    }

    public function testSubclientClientCanGetTesterById() {
        $httpClient = new \CFX\Test\HttpClient();
        $cfx = new \CFX\SDK\Test\Client('https://null.cfxtrading.com', '12345', 'abcde', $httpClient);

        $httpClient->setNextResponse(new \GuzzleHttp\Message\Response(
            200,
            ['Content-Type' => 'application/json'],
            \GuzzleHttp\Stream\Stream::factory(json_encode(self::$testers[0]))
        ));

        $tester = $cfx->testers->get('id=1');
        $this->assertInstanceOf("\\CFX\\Test\\TesterInterface", $tester);
        $this->assertEquals('1', $tester->getId());
    }
}



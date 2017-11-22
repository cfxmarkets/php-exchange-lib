<?php
namespace CFX\SDK\Exchange;

class OrdersClientTest extends \PHPUnit\Framework\TestCase
{
    protected static $config;
    protected $context;
    protected $client;

    public static function setUpBeforeClass()
    {
        static::$config = new Test\Config(__DIR__.'/config.php', __DIR__.'/config.local.php');
    }

    public function setUp()
    {
        $this->context = new Test\Client(static::$config->getBaseExchangeUri(), static::$config->getExchangeApiKey(), static::$config->getExchangeApiKeySecret());
        $this->client = new Test\OrdersClient($this->context);
    }

    public function testResourceType()
    {
        $this->assertEquals('orders', $this->client->getResourceType());
    }

    public function testCreateResource()
    {
        $this->assertInstanceOf('\\CFX\\Exchange\\OrderInterface', $this->client->create());
    }

    public function testGetRejectsInvalidQueries()
    {
        foreach (['cha' => 'cha=fah', 'orderKey' => 'orderKey=12345'] as $k => $v) {
            try {
                $this->client->get($v);
                $this->fail("Should have thrown an exception");
            } catch (\CFX\Persistence\BadQueryException $e) {
                $this->assertContains($k, $e->getMessage());
            }
        }
    }

    public function testGetAcceptsValidQueries()
    {
        $tests = [
            'id' => [
                'id=12345',
                new \GuzzleHttp\Message\Response(200, [], \GuzzleHttp\Stream\Stream::factory(json_encode(Test\Order::$testData[0])))

            ],
            'accountKey' => [
                'accountKey=12345',
                new \GuzzleHttp\Message\Response(200, [], \GuzzleHttp\Stream\Stream::factory(json_encode(Test\Order::$testData)))
            ],
        ];

        foreach ($tests as $k => $test) {
            $this->context->setNextResponse($test[1]);
            $this->client->get($test[0]);
            $this->assertTrue(true, "This is correct: it didn't throw any exceptions");
        }
    }

    public function testCreatesCorrectObjectFromResults()
    {
        $d = Test\Order::$testData[0];
        $r = new \GuzzleHttp\Message\Response(200, [], \GuzzleHttp\Stream\Stream::factory(json_encode($d)));
        $this->context->setNextResponse($r);
        $order = $this->client->get('id=12345');
        $this->assertInstanceOf("\\CFX\\Exchange\\OrderInterface", $order);
        $test = [
            'Side' => $d['orderType'],
            'LotSize' => $d['orderQuantity'],
            'PriceHigh' => null,
            'PriceLow' => null,
            'CurrentPrice' => $d['orderPrice'],
            'Status' => OrdersClient::mapStatus($d['orderStatus']),
            'StatusDetail' => null,
            'DocumentKey' => $d['documentKey'],
            'ReferenceKey' => $d['accountKey'],
            'BankAccountId' => $d['vaultKey'] === 'undefined' ? null : $d['vaultKey'],
        ];

        foreach($test as $m => $v) {
            $m = "get$m";
            $this->assertEquals($v, $order->$m(), "$m returned a value (`".$order->$m()."`) that was not equal to the expected `$v`");
        }

        $this->assertInstanceOf("\\CFX\\Exchange\\AssetInterface", $order->getAsset());
        $this->assertEquals($d['assetSymbol'], $order->getAsset()->getId());
    }

    public function testDelete()
    {
        $r = new \GuzzleHttp\Message\Response(200);
        $this->context->setNextResponse($r);
        $this->client->delete('12345');
        $request = $this->context->getRequestStack();
        $this->assertEquals(1, count($request));
        $request = $request[0];
        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals(static::$config->getBaseExchangeUri()."/v0/orders", $request->getUrl());
        $this->assertEquals("orderKey=12345", (string)$request->getBody());
    }

    public function testSave()
    {
        // NOTE: This is a sell order
        $d = Test\Order::$testData[0];
        $r = new \GuzzleHttp\Message\Response(200, [], \GuzzleHttp\Stream\Stream::factory(json_encode($d)));
        $this->context->setNextResponse($r);

        $order = $this->client->create()
            ->setReferenceKey($d['accountKey'])
            ->setAsset($this->context->assets->create(['id' => $d['assetSymbol']]))
            ->setSide($d['orderType'])
            ->setLotSize($d['orderQuantity'])
            ->setPriceLow(1)
            ->setPriceHigh($d['orderPrice'])
            ->setDocumentKey($d['documentKey'])
            ->save();

        $request = $this->context->getRequestStack();
        $this->assertEquals(1, count($request));
        $request = $request[0];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals(static::$config->getBaseExchangeUri()."/v0/orders", $request->getUrl());

        $test = [
            'accountKey' => $d['accountKey'],
            'assetSymbol' => $d['assetSymbol'],
            'orderType' => $d['orderType'],
            'orderQuantity' => $d['orderQuantity'],
            'orderPrice' => $d['orderPrice'],
            'orderPriceLower' => 1,
            'referenceKey' => $d['accountKey'],
        ];

        $rparams = explode("&", (string)$request->getBody());
        $params = [];
        foreach($rparams as $k => $v) {
            $v = explode("=", $v);
            if (!array_key_exists(1, $v)) {
                $v[1] = null;
            }
            $params[urldecode($v[0])] = urldecode($v[1]);
        }

        foreach ($test as $k => $v) {
            $this->assertContains($k, array_keys($params), "Parameter `$k` should be among the keys sent in the POST request");
            $this->assertEquals($v, $params[$k], "Parameter `$k` should equal `$v`.");
        }


        // Now prepare the next iteration of the object

        $test = [];
        $d['documentKey'] = $test['documentKey'] = '12345';
        $d['vaultKey'] = $test['vaultKey'] = 'abcde';
        $r = new \GuzzleHttp\Message\Response(200, [], \GuzzleHttp\Stream\Stream::factory(json_encode($d)));
        $this->context->setNextResponse($r);

        // Update and save again
        $order
            ->setDocumentKey($test['documentKey'])
            ->setBankAccountId($test['vaultKey'])
            ->save();

        $request = $this->context->getRequestStack();
        $this->assertEquals(1, count($request));
        $request = $request[0];
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals(static::$config->getBaseExchangeUri()."/v0/orders", $request->getUrl());

        $rparams = explode("&", (string)$request->getBody());
        $params = [];
        foreach($rparams as $k => $v) {
            $v = explode("=", $v);
            if (!array_key_exists(1, $v)) {
                $v[1] = null;
            }
            $params[urldecode($v[0])] = urldecode($v[1]);
        }

        // Make sure the orderKey was sent as form data in the body
        $this->assertContains('orderKey', array_keys($params));
        $this->assertEquals($order->getId(), $params['orderKey']);

        foreach ($test as $k => $v) {
            $this->assertContains($k, array_keys($params), "Parameter `$k` should be among the keys sent in the PUT request");
            $this->assertEquals($v, $params[$k], "Parameter `$k` should equal `$v`.");
            unset($params[$k]);
        }

        // Unset the system-set orderKey variable, too
        unset($params['orderKey']);

        $this->assertEquals(0, count($params), "Params should only have contained the changed parameters, but instead contained these other keys: ".json_encode(array_keys($params)));
    }
}


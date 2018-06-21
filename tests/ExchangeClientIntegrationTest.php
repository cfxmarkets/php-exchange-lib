<?php
namespace CFX\SDK\Exchange;

class ExchangeClientIntegrationTest extends \PHPUnit\Framework\TestCase {
    protected static $cnf;
    protected $cfx;
    protected static $createdResources = [
        'assets' => [],
        'orders' => []
    ];

    public static function setUpBeforeClass() {
        self::$cnf = new Test\Config(__DIR__.'/config.php', __DIR__.'/config.local.php');
    }

    public static function tearDownAfterClass()
    {
        $cfx = new Client(
            self::$cnf->getBaseExchangeUri(),
            self::$cnf->getExchangeApiKey(),
            self::$cnf->getExchangeApiKeySecret(),
            new \GuzzleHttp\Client(['http_errors' => false])
        );

        foreach (static::$createdResources as $type => $resources) {
            $ds = $cfx->$type;
            foreach($resources as $r) {
                $ds->delete($r);
            }
        }
    }

    public function setUp()
    {
        $this->cfx = new Client(
            self::$cnf->getBaseExchangeUri(),
            self::$cnf->getExchangeApiKey(),
            self::$cnf->getExchangeApiKeySecret(),
            new \GuzzleHttp\Client(['http_errors' => false])
        );
        $this->cfx->setDebug(true);
    }

    public function testAssetsClientCanGetAllAssets() {
        $assets = $this->cfx->assets->get();
        $this->assertTrue(count($assets) > 0, "Should have returned a full collection of asset objects");
        $this->assertInstanceOf("\\CFX\\Exchange\\AssetInterface", $assets[0]);
    }

    public function testAssetsClientCanGetAssetById() {
        $asset = $this->cfx->assets->get('id=FR008');
        $this->assertInstanceOf("\\CFX\\Exchange\\AssetInterface", $asset);
        $this->assertEquals('FR008', $asset->getId());
    }

    public function testOrdersClientCanGetOrdersForAccountKey()
    {
        $this->markTestIncomplete("This functionality is not yet implemented in the API.");
        $orders = $this->cfx->orders->get('accountKey='.Test\Order::$testData[0]['accountKey']);
        $this->assertNotEquals(0, count($orders));
    }

    public function testOrdersClientCanGetOrderById()
    {
        $order = $this->cfx->orders->get('id='.Test\Order::$testData[0]['orderKey']);
        $this->assertInstanceOf("\\CFX\\Exchange\\OrderInterface", $order);
    }

    public function testOrdersClientCanCreateUpdateDeleteOrder()
    {
        $testData = Test\Order::$testData[0];
        $order = $this->cfx->orders->create()
            ->setReferenceKey($testData['accountKey'])
            ->setAsset($this->cfx->assets->get("id=$testData[assetSymbol]"))
            ->setSide('sell')
            ->setLotSize(12345)
            ->setPriceHigh(2.50)
            ->setPriceLow(1.99)
            ->save();

        $this->assertNotNull($order->getId());
        self::$createdResources['orders'][] = $order->getId();

        $order = $this->cfx->orders->get('id='.$order->getId());
        $this->assertInstanceOf("\\CFX\\Exchange\\OrderInterface", $order);

        $this->assertEquals($testData['accountKey'], $order->getReferenceKey());
        $this->assertEquals($testData['assetSymbol'], $order->getAsset()->getId());
        $this->assertEquals('sell', $order->getSide());
        $this->assertEquals(12345, $order->getLotSize());
        $this->assertNull($order->getDocumentKey());
        $this->assertNull($order->getBankAccountId());

        // NOTE: These price fields really *shouldn't* be this way, but they are because of the way the api currently works
        $this->assertEquals(null, $order->getPriceHigh());
        $this->assertEquals(null, $order->getPriceLow());
        $this->assertEquals(2.50, $order->getCurrentPrice());

        $order
            ->setDocumentKey("12345678")
            ->save();

        $order = $this->cfx->orders->get('id='.$order->getId());

        $this->assertEquals($testData['accountKey'], $order->getReferenceKey());
        $this->assertEquals($testData['assetSymbol'], $order->getAsset()->getId());
        $this->assertEquals('sell', $order->getSide());
        $this->assertEquals(12345, $order->getLotSize());
        $this->assertEquals("12345678", $order->getDocumentKey());
        //$this->assertEquals("5544332211", $order->getBankAccountId());

        $this->assertEquals(null, $order->getPriceHigh());
        $this->assertEquals(null, $order->getPriceLow());
        $this->assertEquals(2.50, $order->getCurrentPrice());

        $this->cfx->orders->delete($order);

        $order = $this->cfx->orders->get('id='.$order->getId());
        $this->assertEquals('cancelled', $order->getStatus());
    }
}




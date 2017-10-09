# CFX Markets Exchange SDK for PHP

*A public PHP SDK library to access the CFX Markets exchange.*

This library helps to facilitate interactions with the CFX Exchange through CFX's exchange REST api.

## Usage

### Instantiation

The CFX Exchange requires the use of an authenticated API key for all interactions. You should provide your API key and secret to the constructor, along with the API url you'd like to target (usually `https://sandbox.apis.cfxtrading.com/exchange` for testing and `https://apis.cfxtrading.com/exchange` for production).

```php
// In your code, where you're planning on using the SDK...
$cfx = new \CFX\SDK\Exchange\Client('https://sandbox.apis.cfxtrading.com/exchange', $apiKey, $secret, $httpClient);
```

### Manipulating Objects

You'll probably primarily be interested in fetching, creating, updating, or deleting data objects. To do this, you'll use the peripheral object classes provided by this library, then send instructions to the server via the Client you instantiated along with (optionally) one of the resource objects you've created.

For example, here's how to get a list of assets from the server. This returns a `ResourceCollection` that contains `Asset` resources:

```php
// Get a list of assets from the server

$assets = $cfx->assets->get();
$asset = $assets[0];
echo $asset->getName();

// ...
```

Here's an example of how you might create an order:

```php
// First, create some peripherals

$asset = $cfx->assets->create(['id' => $_POST['assetSymbol']]);
$qty = $_POST['qty'];

// Then create the order locally (assume that $user is a valid user in your system)
$order = $cfx->orders->create()
    ->setType('sell')
    ->setOwnerToken($user->getToken())
    ->setAsset($asset)
    ->setQuantity($qty);

// Now, send the order to the server, catching any errors thrown
try {
    $order->save();
    $response = ....
} catch (\CFX\BadInputException $e) {
    // Means there were data input errors
} catch (.....) {
    // etc...
}
```


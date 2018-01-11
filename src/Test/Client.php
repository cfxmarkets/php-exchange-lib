<?php
namespace CFX\SDK\Exchange\Test;

class Client extends \CFX\SDK\Exchange\Client
{
    protected $requestStack = [];
    protected $nextResponse = [];

    public function getRequestStack()
    {
        $stack = $this->requestStack;
        $this->requestStack = [];
        return $stack;
    }

    public function setNextResponse(\Psr\Http\Message\ResponseInterface $r)
    {
        $this->nextResponse[] = $r;
    }

    public function sendRequest($method, $endpoint, array $params=[]) {
        // Compose URI
        $uri = $this->composeUri($endpoint);

        // Add Authorization header if necessary

        if (!array_key_exists('headers', $params)) $params['headers'] = [];
        $authz_header = null;
        foreach($params['headers'] as $n => $v) {
            if (strtolower($n) == 'authorization') {
                $authz_header = $n;
                break;
            }
        }

        if (!$authz_header) $params['headers']['Authorization'] = "Basic ".base64_encode("{$this->getApiKey()}:{$this->getApiKeySecret()}");

        $r = new \GuzzleHttp\Psr7\Request($method, $uri, $params['headers']);
        $r = $this->applyRequestOptions($r, $params);
        unset($params['body'], $params['json'], $params['headers'], $params['query']);
        $this->requestStack[] = $r;

        if (count($this->nextResponse) === 0) {
            if (array_key_exists('query', $params)) {
                $q = [];
                foreach($params['query'] as $k => $v) {
                    $q[] = "$k=$v";
                }
                $q = "?".implode("&", $q);
            } else {
                $q = "";
            }
            throw new \RuntimeException("You need to set a response for this request (`$method $endpoint$q`) by calling `setNextResponse` on the REST DataContext you're using.");
        }
        return $this->processResponse(array_pop($this->nextResponse));
    }

}



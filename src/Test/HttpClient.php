<?php
namespace CFX\Test;

class HttpClient extends \GuzzleHttp\Client {
    protected $nextResponse = [];
    protected $requestTrace = [];

    public function setNextResponse(\GuzzleHttp\Message\ResponseInterface $r) {
        $this->nextResponse[] = $r;
        return $this;
    }

    public function getLastRequest() {
        $i = count($this->requestTrace);
        if ($i == 0) return null;
        return $this->requestTrace[$i-1];
    }

    public function send(\GuzzleHttp\Message\RequestInterface $r) {
        if (count($this->nextResponse) == 0) throw new \RuntimeException("This is a test HTTP Client that does not make real HTTP calls. You must set the response for the request you're about to execute by using the `setNextResponse(\GuzzleHttp\Message\ResponseInterface \$r)` method.");

        $this->requestTrace[] = $r;
        $res = array_pop($this->nextResponse);
        return $res;
    }
}


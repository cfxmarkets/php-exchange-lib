<?php
namespace CFX\SDK\Exchange\Test;

class OrdersClient extends \CFX\SDK\Exchange\OrdersClient
{
    public function create(array $data=null, $type = null) {
        return new Order($this, $data);
    }
}


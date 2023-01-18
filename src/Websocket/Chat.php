<?php

declare(strict_types=1);

namespace App\Websocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class Chat implements MessageComponentInterface
{
    protected SplObjectStorage $connections;

    public function __construct() {
        print('in Chat constructor');
        $this->connections = new SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        print('on open');
        $this->connections->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        print('on message');
        foreach($this->connections as $connection)
        {
            if($connection === $from)
            {
                continue;
            }
            $connection->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e) {
        $this->connections->detach($conn);
        $conn->close();
    }
}
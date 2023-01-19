<?php

declare(strict_types=1);

namespace App\Websocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;
use Symfony\Component\Console\Output\OutputInterface;

class Chat implements MessageComponentInterface
{
    protected SplObjectStorage $connections;

    private OutputInterface $output;

    public function __construct(OutputInterface $output) {
        $this->connections = new SplObjectStorage;
        $this->output = $output;

    }

    public function onOpen(ConnectionInterface $conn) {
        $this->output->writeln('new connection');
        $this->connections->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $this->output->writeln('new message : '. $msg);
        foreach($this->connections as $connection)
        {
            $connection->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->output->writeln('connection closed');
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e) {
        $this->output->writeln('new error');
        $this->connections->detach($conn);
        $conn->close();
    }
}
<?php

declare(strict_types=1);

namespace App\Websocket;

use App\Controller\SocketController;
use App\Exception\CharacterEquipmentException;
use App\Exception\FightException;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class WebSocketServer implements MessageComponentInterface
{
    private SocketController $socketController;

    public function __construct(SocketController $socketController) {
        $this->socketController = $socketController;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->socketController->handleOpen($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg): bool
    {
        return $this->socketController->handleMessage($from, $msg);
    }

    public function onClose(ConnectionInterface $conn) {
        $this->socketController->handleClose($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e) {
        $this->socketController->handleError($conn, $e);
    }
}
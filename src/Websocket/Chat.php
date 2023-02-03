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
    private $users = [];
    private $botName = 'ChatBot';
    private $defaultChannel = 'general';

    protected SplObjectStorage $connections;
    private OutputInterface $output;

    public function __construct(OutputInterface $output) {
        $this->connections = new SplObjectStorage;
        $this->output = $output;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->output->writeln('new connection : #' . $conn->resourceId);
        if(array_key_exists($conn->resourceId, $this->users)) {
            $this->users[$conn->resourceId] = [
                'connection' => $conn,
                'user' => '',
                'channels' => []
            ];
            $this->connections->attach($conn);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $this->output->writeln('new message : ' . $msg);
        $messageData = json_decode($msg);
        if ($messageData === null) {
            return false;
        }

        $action = $messageData->action ?? 'unknown';
        $channel = $messageData->channel ?? $this->defaultChannel;
        $user = $messageData->user ?? $this->botName;
        $message = $messageData->message ?? '';

        switch ($action) {
            case 'subscribe':
                $this->subscribeToChannel($from, $channel, $user);
                return true;
            case 'unsubscribe':
                $this->unsubscribeFromChannel($from, $channel, $user);
                return true;
            case 'message':
                return $this->sendMessageToChannel($from, $channel, $user, $message);
            default:
                echo sprintf('Action "%s" is not supported yet!', $action);
                break;
        }
        return false;
    }

    private function subscribeToChannel(ConnectionInterface $conn, $channel, $user)
    {
        $this->users[$conn->resourceId]['channels'][$channel] = $channel;
        $this->sendMessageToChannel(
            $conn,
            $channel,
            $this->botName,
            $user.' joined #'.$channel
        );
    }

    private function unsubscribeFromChannel(ConnectionInterface $conn, $channel, $user)
    {
        if (array_key_exists($channel, $this->users[$conn->resourceId]['channels'])) {
            unset($this->users[$conn->resourceId]['channels']);
        }
        $this->sendMessageToChannel(
            $conn,
            $channel,
            $this->botName,
            $user.' left #'.$channel
        );
    }

    private function sendMessageToChannel(ConnectionInterface $conn, $channel, $user, $message): bool
    {
        if (!isset($this->users[$conn->resourceId]['channels'][$channel])) {
            return false;
        }
        foreach ($this->users as $connectionId => $userConnection) {
            if (array_key_exists($channel, $userConnection['channels'])) {
                $userConnection['connection']->send(json_encode([
                    'action' => 'message',
                    'channel' => $channel,
                    'user' => $user,
                    'message' => $message
                ]));
            }
        }
        return true;
    }

    public function onClose(ConnectionInterface $conn) {
        $this->output->writeln('connection closed');
        unset($this->users[$conn->resourceId]);
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e) {
        $this->output->writeln('new error');
        $this->connections->detach($conn);
        $conn->close();
    }
}
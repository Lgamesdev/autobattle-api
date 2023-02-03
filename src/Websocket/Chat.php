<?php

declare(strict_types=1);

namespace App\Websocket;

use Exception;
use FTP\Connection;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Chat implements MessageComponentInterface
{
    private $users = [];
    private $botName = 'ChatBot';
    private $defaultChannel = 'general';

    private TokenStorageInterface $tokenStorageInterface;
    private JWTTokenManagerInterface $jwtManager;
    protected SplObjectStorage $connections;
    private OutputInterface $output;

    public function __construct(OutputInterface $output, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager) {
        $this->connections = new SplObjectStorage;
        $this->output = $output;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->jwtManager = $jwtManager;
    }

    public function onOpen(ConnectionInterface $conn) {
        $username = $this->getUsername($conn);

        $this->output->writeln('new connection from : ' . $username . ' #' . $conn->resourceId);

        if(array_key_exists($username, $this->users)) {
            $this->connections->detach($this->users[$username]['connection']);
            $this->users[$username]['connection']->close();
            unset($this->users[$username]);
        }

        $this->users[$username] = [
            'connection' => $conn,
            'user' => $username,
            'channels' => []
        ];

        $this->connections->attach($conn);
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
        $this->users[$this->getUsername($conn)]['channels'][$channel] = $channel;
        $this->sendMessageToChannel(
            $conn,
            $channel,
            $this->botName,
            $user.' joined #'.$channel
        );
    }

    private function unsubscribeFromChannel(ConnectionInterface $conn, $channel, $user)
    {
        if (array_key_exists($channel, $this->users[$this->getUsername($conn)]['channels'])) {
            unset($this->users[$this->getUsername($conn)]['channels']);
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
        if (!isset($this->users[$this->getUsername($conn)]['channels'][$channel])) {
            return false;
        }
        foreach ($this->users as $username => $userConnection) {
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
        unset($this->users[$this->getUsername($conn)]);
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e) {
        $this->output->writeln('new error : ' . $e);
        $this->connections->detach($conn);
        $conn->close();
    }

    public function getUsername(ConnectionInterface $conn) : string
    {
        $headers = $conn->httpRequest->getHeaders();
        return $this->jwtManager->parse($headers['Authorization'][0])['username'];
    }
}
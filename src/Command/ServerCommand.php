<?php

declare(strict_types=1);

namespace App\Command;

use App\Controller\SocketController;
use App\Websocket\WebSocketServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

#[AsCommand(name: 'run:websocket-server')]
class ServerCommand extends Command
{
    private EventDispatcherInterface $dispatcher;
    private SocketController $socketController;

    public function __construct(EventDispatcherInterface $dispatcher, SocketController $socketController) {
        parent::__construct();
        $this->dispatcher = $dispatcher;
        $this->socketController = $socketController;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = 5120;

        $this->dispatcher->addListener(
            'console.writeline',
            function (GenericEvent $event) use ($output) {
                $output->writeLn($event->getSubject());
            }
        );

        $output->writeln("Starting server on port " . $port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketServer($this->socketController)
                )
            ),
            $port
        );
        $server->run();
        return 0;
    }
}
<?php

declare(strict_types=1);

namespace App\Command;

use App\Websocket\Chat;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    protected static $defaultName = "run:websocket-server";

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = 5120;
        $output->writeln("Starting server on port " . $port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new Chat()
                )
            ),
            $port
        );
        $server->run();
        return 0;
    }
}
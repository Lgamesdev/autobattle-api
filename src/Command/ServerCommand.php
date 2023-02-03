<?php

declare(strict_types=1);

namespace App\Command;

use App\Websocket\Chat;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsCommand(name: 'run:websocket-server')]
class ServerCommand extends Command
{
    private TokenStorageInterface $tokenStorageInterface;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager) {
        parent::__construct();
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->jwtManager = $jwtManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = 5120;
        $output->writeln("Starting server on port " . $port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new Chat($output, $this->tokenStorageInterface, $this->jwtManager)
                )
            ),
            $port
        );
        $server->run();
        return 0;
    }
}
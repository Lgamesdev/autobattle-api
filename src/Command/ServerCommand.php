<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Chat;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ratchet\App;
use Ratchet\Http\HttpServer;
use Ratchet\Server\EchoServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:chat:server',
    description: 'Start websocket server',
    aliases: ['app:start:server'],
    hidden: false
)]
class ServerCommand extends Command
{
    // the command description shown when running "php bin/console list"
    protected static $defaultDescription = 'Start websocket server';

    public function __construct()
    {
        parent::__construct();
    }

    // ...
    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to start websocket server...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Server Start',
            '============',
            '',
        ]);

        $app = new App('localhost', 8080);
        $app->route('/chat', new Chat(), array('*'));
        $app->route('/echo', new EchoServer(), array('*'));
        $app->run();

        // outputs a message followed by a "\n"
        $output->writeln('Whoa!');

        // outputs a message without adding a "\n" at the end of the line
        $output->write('Server started.');

        return Command::SUCCESS;
    }
}
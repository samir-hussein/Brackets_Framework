<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JWTSecret extends Command
{
    protected $commandName = 'generate:jwt-secret';
    protected $commandDescription = "generate jwt secret key";

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = new \MirazMac\DotEnv\Writer(__DIR__ . '/../' . '.env');
        $env->set('JWT_SECRET', "'" . token(15) . "'");
        $env->write();
        $output->writeln('Generated Successfully');
        return 0;
    }
}

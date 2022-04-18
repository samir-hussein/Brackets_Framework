<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppKey extends Command
{
    protected $commandName = 'key:generate';
    protected $commandDescription = "generate app secret key";

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = new \MirazMac\DotEnv\Writer(__DIR__ . '/../' . '.env');
        $env->set('APP_KEY', "'" . token(20) . "'");
        $env->write();
        $output->writeln('App key Generated Successfully');
        return 0;
    }
}

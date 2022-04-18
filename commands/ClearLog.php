<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearLog extends Command
{
    protected $commandName = 'clear:log';
    protected $commandDescription = "clear log file";

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        file_put_contents(__DIR__ . '/../storage/log/brackets.log', "");
        $output->writeln('Log file cleared Successfully');
        return 0;
    }
}

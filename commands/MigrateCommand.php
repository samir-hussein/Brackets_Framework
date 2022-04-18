<?php

namespace Commands;

require_once 'migration.php';

use migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    protected $commandName = 'migrate';
    protected $commandDescription = "Create Migrations";

    protected $commandOptionName = "refresh";
    protected $commandOptionDescription = 'Refresh all migrations';

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription)
            ->addOption(
                $this->commandOptionName,
                null,
                InputOption::VALUE_NONE,
                $this->commandOptionDescription
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $obj = new migration;
        if ($input->getOption($this->commandOptionName)) {
            $obj->refresh();
            return 0;
        }

        $obj->migrate();
        return 0;
    }
}

<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends Command
{
    protected $commandName = 'serve';
    protected $commandDescription = "Server Run";

    // protected $commandArgumentName = "";
    // protected $commandArgumentDescription = "";

    // protected $commandOptionName = ""; // should be specified like "app:greet John --cap"
    // protected $commandOptionDescription = '';

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription);
        // ->addArgument(
        //     $this->commandArgumentName,
        //     InputArgument::OPTIONAL,
        //     $this->commandArgumentDescription
        // )
        // ->addOption(
        //     $this->commandOptionName,
        //     null,
        //     InputOption::VALUE_NONE,
        //     $this->commandOptionDescription
        // );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $name = $input->getArgument($this->commandArgumentName);

        // if ($name) {
        //     $text = 'Hello ' . $name;
        // } else {
        //     $text = 'Hello';
        // }

        // if ($input->getOption($this->commandOptionName)) {
        //     $text = strtoupper($text);
        // }

        // $output->writeln($text);
        chdir('public');
        shell_exec('php -S 127.0.0.1:8080');
    }
}

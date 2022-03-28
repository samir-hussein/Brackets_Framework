<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends Command
{
    protected $commandName = 'make:controller';
    protected $commandDescription = "Make Controller";

    protected $commandArgumentName = "name";
    protected $commandArgumentDescription = "Controller Name";

    protected $commandOptionName = "r"; // should be specified like "make:controller ControllerName --r"
    protected $commandOptionDescription = 'Make Resource Methods (index, store, update, show, destroy)';

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription)
            ->addArgument(
                $this->commandArgumentName,
                InputArgument::OPTIONAL,
                $this->commandArgumentDescription
            )
            ->addOption(
                $this->commandOptionName,
                null,
                InputOption::VALUE_NONE,
                $this->commandOptionDescription
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument($this->commandArgumentName);

        if ($name) {
            if (file_exists("controllers/$name.php")) {
                $output->writeln('Controller is already exists');
            } else {
                shell_exec("touch controllers/$name.php");
                $myfile = fopen("controllers/$name.php", "w") or die("Unable to open file!");
                $txt = "<?php\n\n";
                $txt .= "namespace Controllers;\n\n";
                $txt .= "class $name\n";
                $txt .= "{\n\n";
                if ($input->getOption($this->commandOptionName)) {
                    $txt .= "   public function index()\n";
                    $txt .= "   {\n\n   }\n\n";
                    $txt .= "   public function store()\n";
                    $txt .= "   {\n\n   }\n\n";
                    $txt .= "   public function show()\n";
                    $txt .= "   {\n\n   }\n\n";
                    $txt .= "   public function update()\n";
                    $txt .= "   {\n\n   }\n\n";
                    $txt .= "   public function destroy()\n";
                    $txt .= "   {\n\n   }\n\n";
                }
                $txt .= "}\n";
                fwrite($myfile, $txt);
                fclose($myfile);
                $output->writeln('Controller Created Successfully');
            }
        } else {
            $output->writeln('Incompleted Command');
        }
        return 0;
    }
}

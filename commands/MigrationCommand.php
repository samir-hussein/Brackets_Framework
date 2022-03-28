<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationCommand extends Command
{
    protected $commandName = 'make:migration';
    protected $commandDescription = "Make Migration";

    protected $commandArgumentName = "name";
    protected $commandArgumentDescription = "Migration Name";

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription)
            ->addArgument(
                $this->commandArgumentName,
                InputArgument::OPTIONAL,
                $this->commandArgumentDescription
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument($this->commandArgumentName);

        if ($name) {
            if (file_exists("core/Database_Tables/$name.php")) {
                $output->writeln('Migration is already exists');
            } else {
                shell_exec("touch core/Database_Tables/$name.php");
                $myfile = fopen("core/Database_Tables/$name.php", "w") or die("Unable to open file!");
                $name = strtolower($name);
                $txt = "<?php\n\nuse App\Database\Schema;\n\nSchema::create('$name', function (" . '$table' . ") {\n" . '    $table->id()' . ";\n});";
                fwrite($myfile, $txt);
                fclose($myfile);
                $output->writeln('Migration Created Successfully');
            }
        } else {
            $output->writeln('Incompleted Command');
        }
        return 0;
    }
}

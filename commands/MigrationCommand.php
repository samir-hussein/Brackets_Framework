<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            if (file_exists("migrations/$name.php")) {
                $output->writeln('Migration is already exists');
            } else {
                shell_exec("touch migrations/$name.php");
                $myfile = fopen("migrations/$name.php", "w") or die("Unable to open file!");
                $txt = "<?php\n\nnamespace Migrations;\n\nuse App\Database\Schema;\n\n";
                $txt .= "class $name extends Schema\n{\n";
                $name = strtolower($name);
                $txt .= "   public function up()\n";
                $txt .= "   {\n";
                $txt .= '       $this->create("' . $name . '", function (Schema $table) {' . "\n";
                $txt .= '           $table->id();' . "\n";
                $txt .= "       });\n   }\n\n";
                $txt .= "   public function down()\n";
                $txt .= "   {\n";
                $txt .= '       $this->dropTable("' . $name . '");' . "\n   }\n}";
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

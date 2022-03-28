<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ModelCommand extends Command
{
    protected $commandName = 'make:model';
    protected $commandDescription = "Make Model";

    protected $commandArgumentName = "name";
    protected $commandArgumentDescription = "Model Name";

    protected $commandOptionName = "a"; // should be specified like "make:controller ControllerName --r"
    protected $commandOptionDescription = 'Make model, controller and migration';

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
            if (file_exists("models/$name.php")) {
                $output->writeln('Model is already exists');
            } else {
                shell_exec("touch models/$name.php");
                $myfile = fopen("models/$name.php", "w") or die("Unable to open file!");
                $txt = "<?php\n\n";
                $txt .= "namespace Models;\n\n";
                $txt .= "use App\Database\Eloquent;\n\n";
                $txt .= "class $name extends Eloquent\n";
                $txt .= "{\n";
                $txt .= "   public function __construct()\n";
                $txt .= "   {\n";
                $txt .= "       " . 'self::$tableName' . " = '';\n";
                $txt .= "       " . 'self::$columnNames' . " = [];\n";
                $txt .= "   }\n}";
                fwrite($myfile, $txt);
                fclose($myfile);
                if ($input->getOption($this->commandOptionName)) {
                    $controller = $name . 'Controller';
                    if (file_exists("controllers/$controller.php")) {
                        $output->writeln('Controller is already exists');
                    } else {
                        shell_exec("touch controllers/$controller.php");
                        $myfile = fopen("controllers/$controller.php", "w") or die("Unable to open file!");
                        $txt = "<?php\n\n";
                        $txt .= "namespace Controllers;\n\n";
                        $txt .= "class $controller\n";
                        $txt .= "{\n\n";
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
                        $txt .= "}\n";
                        fwrite($myfile, $txt);
                        fclose($myfile);
                        $output->writeln('Controller Created Successfully');
                    }

                    $name = $name . 's';
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
                }
                $output->writeln('Model Created Successfully');
            }
        } else {
            $output->writeln('Incompleted Command');
        }
        return 0;
    }
}

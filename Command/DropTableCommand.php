<?php


namespace garinlu\SQLServerBundle\Command;


use CommonBundle\Utils\API\SQLServerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DropTableCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('gl:sqlserver:drop')
            // the short description shown while running "php bin/console list"
            ->setDescription('Drop a table in the SQLServer database of Save The Children')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Drop a table in the SQLServer database of Save The Children');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            "",
            "====================================================================================",
            "<info>Drop a table in the SQLServer database of Save The Children</info>",
            "<comment>This command will try to drop a table in SQL Server of 'savechildren.org'.</comment>",
            "====================================================================================",
            ""
        ]);
        $SQLService = $this->getContainer()->get('gl.sql_server_service');
        $connection = $SQLService->connectTo();

        $helper = $this->getHelper('question');
        $questionNameTable = new Question('Please enter the name of table : ');
        $tableName = $helper->ask($input, $output, $questionNameTable);

        $output->writeln([
            "<comment>Check if table name '$tableName' exist.</comment>"
        ]);

        $tableSQLServer = $SQLService->showTables($connection, ['TABLE_NAME' => $tableName]);

        if (empty($tableSQLServer))
        {
            $output->writeln([
                "<error>ERROR!</error>",
                "'$tableName' was not found"
            ]);
            return;
        }

        $output->writeln([
            "<info>The table was found!</info>",
            "",
        ]);
        $output->writeln([
            "<info>DO YOU REALLY WANT TO DELETE THE TABLE '$tableName' ? (y/n)</info>",
            "",
        ]);

        $helper = $this->getHelper('question');
        $want = new Question('');
        $responseWant = $helper->ask($input, $output, $want);
        if (strtolower($responseWant) === 'y')
        {
            $SQLService->dropTable($connection, $tableName);
        }
        else
        {
            $output->writeln([
                "<info>DROPPING CANCELED!</info>",
                "",
            ]);
            return;
        }


        $output->writeln('<info>END!</info>');
    }

}
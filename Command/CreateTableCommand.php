<?php


namespace garinlu\SQLServerBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateTableCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('gl:sqlserver:create')
            // the short description shown while running "php bin/console list"
            ->setDescription('Create a table in the SQLServer database of Save The Children')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Create a table in the SQLServer database of Save The Children');
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
            "<info>Create a table in the SQLServer database of Save The Childre</info>",
            "<comment>This command will try to create a new table with columns (and primary/foreign keys needed)</comment>",
            "<comment>SQL Server of 'savechildren.org'.</comment>",
            "====================================================================================",
            ""
        ]);

        $SQLService = $this->getContainer()->get('gl.sql_server_service');
        $connection = $SQLService->connectTo();

        $helper = $this->getHelper('question');
        $questionNameTable = new Question('Please enter the name of table : ');
        $tableName = $helper->ask($input, $output, $questionNameTable);

        $output->writeln([
            "",
            "<comment>Check if table name '$tableName' doesn't exist yet.</comment>"
        ]);

        $tableSQLServer = $SQLService->showTables($connection, ['TABLE_NAME' => $tableName]);

        if (!empty($tableSQLServer))
        {
            $output->writeln([
                "<error>ERROR!</error>",
                "'$tableName' isset already"
            ]);
            return;
        }

        $output->writeln([
            "<info>The name of the table is free!</info>",
            "",
        ]);
        $columns = [];

        $output->writeln([
            "",
            "<comment>Adding column(s)......</comment>",
        ]);
        do
        {
            $output->writeln([
                "",
                "",
            ]);
            $questionNameColumn = new Question("Please enter the name of column (write y if you don't want to add more column) : ");
            $columnName = $helper->ask($input, $output, $questionNameColumn);
            if (strtolower($columnName) === 'y' || strtolower($columnName) === '')
                break;

            $questionTypeColumn = new Question("Please enter the type of the column '$columnName' : ", 'varchar(255)');
            $columnType = strtoupper($helper->ask($input, $output, $questionTypeColumn));
            $columns[] = [$columnName => $columnType];

        } while (strtolower($columnName) !== 'y' && strtolower($columnName) !== '');

        $foreignKey = [];
        do
        {
            $output->writeln([
                "",
                "",
            ]);
            $questionForeignKey = new Question("Please enter a instruction (like : 'FOREIGN KEY (PersonID) REFERENCES Persons(PersonID)') :");
            $instruction = $helper->ask($input, $output, $questionForeignKey);
            if (strtolower($instruction) === 'y' || strtolower($instruction) === '')
                break;
            $foreignKey[] = $instruction;

        } while (strtolower($instruction) !== 'y' && strtolower($instruction) !== '');

        $SQLService->createTable($connection, $tableName, $columns, $foreignKey);


        $output->writeln('<info>END!</info>');
    }

}
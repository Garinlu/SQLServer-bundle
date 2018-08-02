<?php


namespace garinlu\SQLServerBundle\Command;


use garinlu\SQLServerBundle\Utils\SQLServerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question as QuestionSF;

class GetStructureTableCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('gl:sqlserver:structure')
            // the short description shown while running "php bin/console list"
            ->setDescription('Show structure of a table')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command will print the structure (columns) of a table of SQL Server.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            "",
            "====================================================================================",
            "<info>Show structure of a table</info>",
            "<comment>This command will print the structure (columns) of a table of SQL Server.</comment>",
            "====================================================================================",
            ""
        ]);

        $output->writeln([
            "",
            "====================================================================================",
            "<comment>Connection to the SQL Server....</comment>",
        ]);
        $SQLService = $this->getContainer()->get('gl.sql_server_service');
        $connection = $SQLService->connectTo();

        $output->writeln([
            "<info>Connected!</info>",
            "====================================================================================",
            ""
        ]);
        /** @var SQLServerService $sqlSrvService */
        $sqlSrvService = $this->getContainer()->get('gl.sql_server_service');

        do
        {
            $helper = $this->getHelper('question');
            $questionTableName = new QuestionSF('Table name (To get the list of tables, write nothing) : ');
            $tablename = $helper->ask($input, $output, $questionTableName);

            if ("" == $tablename || null == $tablename)
            {
                $data = $sqlSrvService
                    ->get(
                        $connection,
                        "INFORMATION_SCHEMA.TABLES",
                        ["TABLE_NAME"],
                        ["TABLE_TYPE = 'BASE TABLE'"]
                    );

                if (!empty($data) && $data != "")
                {
                    foreach ($data as $index => $datum)
                    {

                        foreach ($datum as $ind => $item)
                        {
                            if (is_numeric($ind))
                                unset($datum[$ind]);
                        }

                        $i = $index + 1;
                        $output->writeln([
                            "",
                            "<comment>Table $i :</comment>",
                            "<info>{$datum["TABLE_NAME"]}</info>",
                        ]);
                        $output->writeln([
                            "======================================"
                        ]);
                    }
                }

            }
            else
            {
                $output->writeln([
                    "",
                    "<comment>Loading......</comment>",
                ]);


                $data = $sqlSrvService
                    ->get(
                        $connection,
                        "[INFORMATION_SCHEMA].[COLUMNS]",
                        ["COLUMN_NAME", "COLUMN_DEFAULT", "IS_NULLABLE", "DATA_TYPE", "CHARACTER_MAXIMUM_LENGTH"],
                        ["TABLE_NAME='$tablename'"]
                    );

                if (!empty($data) && $data != "")
                {
                    foreach ($data as $index => $datum)
                    {

                        foreach ($datum as $ind => $item)
                        {
                            if (is_numeric($ind))
                                unset($datum[$ind]);
                        }

                        $i = $index + 1;
                        $output->writeln([
                            "",
                            "<info>Column $i :</info>",
                        ]);
                        dump($datum);
                        $output->writeln([
                            "===================================================================================="
                        ]);
                    }
                }
                else
                {
                    $output->writeln([
                        "",
                        "<error>ERROR!</error>",
                        "No data or table found.",
                        ""
                    ]);
                }
                $output->writeln([
                    "",
                    "",
                    "====================================================================================",
                    "<info>END!</info>",
                    "===================================================================================="
                ]);
            }
        } while ("" == $tablename || null == $tablename);
    }
}
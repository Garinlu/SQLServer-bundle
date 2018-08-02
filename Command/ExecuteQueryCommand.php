<?php


namespace garinlu\SQLServerBundle\Command;


use CommonBundle\Utils\API\SQLServerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ExecuteQueryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('gl:sqlserver:query')
            // the short description shown while running "php bin/console list"
            ->setDescription('Execute a query on SQL Server')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Try to execute a query SQL on the SQL Server configured.');
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
            "<info>Execute a query on SQL Server</info>",
            "<comment>Try to execute a query SQL on the SQL Server configured.</comment>",
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
            "",
            ""
        ]);
        $helper = $this->getHelper('question');
        $questionQuery = new Question('SQL query :');
        $query = $helper->ask($input, $output, $questionQuery);


        $output->writeln([
            "The query : ",
            $query,
        ]);
        $questionValid = new Question('Continue (y/n) ?');
        $valid = $helper->ask($input, $output, $questionValid);

        if ($valid == 'y')
        {
            $output->writeln([
                "<comment>Executing the query..</comment>"
            ]);

            $tableSQLServer = $SQLService->execute($connection, $query);

            dump($tableSQLServer);
        }



        $output->writeln([
            '',
            '<info>END!</info>'
        ]);
    }
}
<?php


namespace AppBundle\Command;


use Doctrine\DBAL\Connection;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurePostgresqlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('zigotoo:database:configure')
            ->setDescription('Set les variables de session comme la timezone...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');
        /** @var Connection $connection */
        $connection = $this->getContainer()->get('database_connection');

        $logger->info('Host :     ' . $connection->getHost());
        $logger->info('Database : ' . $connection->getDatabase());
        $logger->info('Username : ' . $connection->getUsername());

        $timezone = ini_get('date.timezone');
        $logger->notice('Configuration de la timezone à ' . $timezone);
        $connection->exec('SET TIME ZONE \'' . $timezone . '\';');

        return 0;
    }
}
<?php


namespace AppBundle\Tests\Command;


use AppBundle\Command\ConfigurePostgresqlCommand;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigurePostgresqlCommandTest extends KernelTestCase
{

    public function setup()
    {
        static::bootKernel();
    }
    public function test()
    {
        $application = new Application(static::$kernel);
        $command = new ConfigurePostgresqlCommand();
        $application->add($command);
        $commandTester = new CommandTester($command);

        /** @var Connection $connection */
        $connection = static::$kernel->getContainer()->get('database_connection');
        
        $connection->exec('SET TIME ZONE \'Europe/Rome\';');

        $this->assertEquals('Europe/Rome', $connection->fetchArray('SHOW TimeZone;')[0]);

        $commandTester->execute([]);

        $this->assertEquals(ini_get('date.timezone'), $connection->fetchArray('SHOW TimeZone;')[0]);
    }
}
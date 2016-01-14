<?php


namespace AppBundle\Tests\Command;


use AppBundle\Command\PageEleveurCommand;
use AppBundle\Tests\TestUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PageEleveurCommandTest extends WebTestCase
{
    /**
     * @expectedException \Exception
     */
    public function testInvalidId()
    {
        $client = static::createClient();
        $application = new Application($client->getKernel());
        $command = new PageEleveurCommand();
        $application->add($command);

        // utilisation de la commande pour supprimer la page eleveur
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'id' => -1
        ));
    }

    public function testDelete()
    {
        // création d'un nouvel utilisateur avec une page eleveur
        $client = static::createClient();
        $pageEleveur = (new TestUtils($client, $this))->createUser()->toEleveur()->getPageEleveur();

        $application = new Application($client->getKernel());
        $command = new PageEleveurCommand();
        $application->add($command);

        // sauvegarde de l'id de la page eleveur
        $pageEleveurId = $pageEleveur->getId();

        // utilisation de la commande pour supprimer la page eleveur
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'id' => $pageEleveurId
        ));

        // verification de la suppression
        /** @var EntityManager $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $pageEleveurRepository = $entityManager->getRepository('AppBundle:PageEleveur');
        $this->assertNull($pageEleveurRepository->find($pageEleveurId));
    }
}
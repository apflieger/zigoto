<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 16/11/15
 * Time: 12:06
 */

namespace AppBundle\Command;


use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Entity\User;
use AppBundle\Service\PageEleveurService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PageEleveurCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('zigoto:page-eleveur:lorem')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'identifiant de la page eleveur'
            )
            ->setDescription('Commit une page eleveur bidon');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var PageEleveurService $pageEleveurService
         */
        $pageEleveurService = $this->getContainer()->get('page_eleveur');

        $pageEleveur = $pageEleveurService->get($input->getArgument('id'));

        if (!$pageEleveur)
        {
            $output->writeln('Page eleveur ' . $input->getArgument('id') . ' n\'exiset pas.');
            return 1;
        }

        /**
         * @var \FOS\UserBundle\Doctrine\UserManager $userManager
         */
        $userManager = $this->getContainer()->get('fos_user.user_manager');

        $commandLineUser = $userManager->findUserByUsername('CommandLine');

        if (!$commandLineUser)
        {
            $commandLineUser = $userManager->createUser();
            $commandLineUser->setUsername('CommandLine');
            $commandLineUser->setEmail('CommandLine@zigoto.com');
            $commandLineUser->setPlainPassword('tatouine');
            /**
             * @var EntityManager $entityManager
             */
            $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
            $entityManager->persist($commandLineUser);
            $entityManager->flush($commandLineUser);
            $output->writeln('Création du user CommandLine('.$commandLineUser->getId().').');
        }

        $commit = new PageEleveurCommit($pageEleveur->getNom(), 'lorem ipsum', $pageEleveur->getCommit());
        $pageEleveurService->commit($pageEleveur->getId(), $commit, $commandLineUser);

        $output->writeln('Page eleveur commitée');
    }


}
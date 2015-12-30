<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 16/11/15
 * Time: 12:06
 */

namespace AppBundle\Command;


use AppBundle\Entity\ERole;
use AppBundle\Entity\User;
use AppBundle\Service\PageEleveurService;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PageEleveurCommand extends ContainerAwareCommand
{
    const CMD_DELETE = 'delete';

    /**
     * @var PageEleveurService
     */
    private $pageEleveurService;

    /**
     * @var \FOS\UserBundle\Model\UserInterface
     */
    private $commandLineUser;

    protected function configure()
    {
        $this
            ->setName('zigoto:page-eleveur:delete')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'identifiant de la page eleveur')
            ->setDescription('Supprime la page eleveur de facon consistante')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pageEleveurService = $this->getContainer()->get('page_eleveur');

        $this->commandLineUser = $this->ensureCommandLineUser($output);

        $pageEleveur = $this->pageEleveurService->get($input->getArgument('id'));

        if (!$pageEleveur)
            throw new Exception('Page eleveur ' . $input->getArgument('id') . ' n\'exiset pas.');

        $pageEleveur->getOwner()->removeRole(ERole::ELEVEUR);
        /**
         * @var \FOS\UserBundle\Doctrine\UserManager $userManager
         */
        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $userManager->updateUser($pageEleveur->getOwner());

        /**
         * @var \Doctrine\ORM\EntityManager $doctrine
         */
        $doctrine = $this->getContainer()->get('doctrine.orm.entity_manager');
        $reflogs = $doctrine->getRepository('AppBundle:PageEleveurReflog')->findBy(
            array('pageEleveur' => $pageEleveur)
        );

        foreach ($reflogs as $reflog) {
            $doctrine->remove($reflog);
        }

        $doctrine->remove($pageEleveur);
        $doctrine->flush();
        return 0;
    }

    /**
     * @param OutputInterface $output
     * @return \FOS\UserBundle\Model\UserInterface
     */
    private function ensureCommandLineUser(OutputInterface $output)
    {
        /**
         * @var \FOS\UserBundle\Doctrine\UserManager $userManager
         */
        $userManager = $this->getContainer()->get('fos_user.user_manager');

        /** @var User $commandLineUser */
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
            $output->writeln('1ere utilisation de la commande');
            $output->writeln('Création du user CommandLine id '.$commandLineUser->getId());
        }
        return $commandLineUser;
    }
}
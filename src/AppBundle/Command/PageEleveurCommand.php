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
use AppBundle\Service\HistoryService;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PageEleveurCommand extends ContainerAwareCommand
{
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
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $pageEleveurRepository = $entityManager->getRepository('AppBundle:PageEleveur');

        $pageEleveur = $pageEleveurRepository->find($input->getArgument('id'));

        if (!$pageEleveur)
            throw new Exception('Page eleveur ' . $input->getArgument('id') . ' n\'exiset pas.');

        /**
         * @var \Doctrine\ORM\EntityManager $doctrine
         */
        $doctrine = $this->getContainer()->get('doctrine.orm.entity_manager');

        $doctrine->remove($pageEleveur);
        $doctrine->flush();
        return 0;
    }
}
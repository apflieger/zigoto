<?php


namespace AppBundle\Controller;


use AppBundle\Entity\PageEleveurBranch;
use AppBundle\Repository\PageEleveurBranchRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\Router;


/**
 * @Route(service="zigotoo.sitemap_controller")
 */
class SitemapController
{
    /** @var TwigEngine */
    private $templating;

    /** @var PageEleveurBranchRepository */
    private $peBranchRepository;

    /** @var Logger */
    private $logger;

    public function __construct(
        PageEleveurBranchRepository $peBranchRepository,
        TwigEngine $templating,
        Logger $logger
    ) {
        $this->templating = $templating;
        $this->peBranchRepository = $peBranchRepository;
        $this->logger = $logger;
    }

    /**
     * @Route("/sitemap.xml", name="sitemap_route", defaults={"_format"="xml"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSitemapAction()
    {
        /** @var PageEleveurBranch[] $pageEleveurBranchess */
        $pageEleveurBranches = $this->peBranchRepository->findAll();

        $this->logger->info('generation de la sitemap', ['count' => count($pageEleveurBranches)]);
        $this->logger->debug('contenu de la sitemap', ['pageEleveurBranches' => $pageEleveurBranches]);

        return $this->templating->renderResponse('sitemap.xml.twig', [
            'pageEleveurBranches' => $pageEleveurBranches
        ]);
    }
}
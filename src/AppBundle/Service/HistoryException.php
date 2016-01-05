<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 12/11/15
 * Time: 15:45
 */

namespace AppBundle\Service;


use AppBundle\Entity\BranchInterface;
use AppBundle\Entity\CommitInterface;
use Exception;

class HistoryException extends Exception
{
    const BRANCHE_INCONNUE = 1;
    const NON_FAST_FORWARD = 2;

    /**
     * @var string
     */
    private $type;

    public function __construct($type)
    {
        parent::__construct();
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 12/11/15
 * Time: 15:45
 */

namespace AppBundle\Service;


use Exception;

class HistoryException extends Exception
{
    const BRANCHE_INCONNUE = 1;
    const DROIT_REFUSE = 2;
    const NON_FAST_FORWARD = 3;
    const NOM_INVALIDE = 4;
    const SLUG_DEJA_EXISTANT = 5;

    public function __construct($code)
    {
        parent::__construct('', $code);
    }

}
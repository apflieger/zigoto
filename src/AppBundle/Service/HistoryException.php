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
    const DEJA_OWNER = 6;

    public function __construct($code)
    {
        parent::__construct($this->map($code), $code);
    }

    private function map($code)
    {
        switch ($code) {
            case static::BRANCHE_INCONNUE:
                return 'BRANCHE_INCONNUE';
            case static::DROIT_REFUSE:
                return 'DROIT_REFUSE';
            case static::NON_FAST_FORWARD:
                return 'NON_FAST_FORWARD';
            case static::NOM_INVALIDE:
                return 'NOM_INVALIDE';
            case static::SLUG_DEJA_EXISTANT:
                return 'SLUG_DEJA_EXISTANT';
            case static::DEJA_OWNER:
                return 'DEJA_OWNER';
        }
    }
}
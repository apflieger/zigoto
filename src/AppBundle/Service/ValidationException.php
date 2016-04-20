<?php


namespace AppBundle\Service;


class ValidationException extends \Exception
{
    const EMPTY_NOM = 1;
    const EMPTY_DATE_NAISSANCE = 2;

    public function __construct($code)
    {
        parent::__construct('', $code);
    }
}
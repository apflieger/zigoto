<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 22:36
 */

namespace AppBundle\Service;


use DateTimeImmutable;

class TimeService
{

    /**
     * @return DateTimeImmutable
     */
    public function now()
    {
        return new DateTimeImmutable();
    }
}
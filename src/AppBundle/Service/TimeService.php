<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 22:36
 */

namespace AppBundle\Service;


use DateTime;

class TimeService
{

    /**
     * @return DateTime
     */
    public function now()
    {
        return new DateTime();
    }
}
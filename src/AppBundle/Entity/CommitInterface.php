<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 27/12/2015
 * Time: 20:46
 */

namespace AppBundle\Entity;


interface CommitInterface
{
    /**
     * @return CommitInterface | null
     */
    public function getParent();

}
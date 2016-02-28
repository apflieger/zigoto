<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 22:37
 */

namespace AppBundle\Tests;


use AppBundle\Service\TimeService;
use DateTimeImmutable;

class TestTimeService extends TimeService
{
    /** @var DateTimeImmutable */
    private $now;

    /**
     * @return DateTimeImmutable
     */
    public function now()
    {
        return $this->now != null ? $this->now : parent::now();
    }

    public function lockNow(DateTimeImmutable $now = null)
    {
        if ($now != null)
            $this->now = $now;
        else
            $this->now = new DateTimeImmutable();
    }

    public function unlockNow()
    {
        $this->now = null;
    }
}
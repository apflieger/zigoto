<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 22:37
 */

namespace AppBundle\Tests;


use AppBundle\Service\TimeService;
use DateTime;
use DateTimeImmutable;

class TestTimeService extends TimeService
{
    /** @var DateTime */
    private $now;

    /**
     * @return DateTime
     */
    public function now()
    {
        return $this->now != null ? clone $this->now : parent::now();
    }

    public function lockNow(DateTime $now = null)
    {
        if ($now != null)
            $this->now = clone $now;
        else
            $this->now = new DateTime();
    }

    public function unlockNow()
    {
        $this->now = null;
    }
}
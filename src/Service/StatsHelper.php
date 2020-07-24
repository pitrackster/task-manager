<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\HolidayRepository;

class StatsHelper
{
    protected $em;
    protected $hrepository;

    public function __construct(
        EntityManagerInterface $em,
        HolidayRepository $hrepository
    ) {
        $this->em = $em;
        $this->hrepository = $hrepository;
    }


    public function getWorkingDaysForPeriod(\DateTime $start, \DateTime $end)
    {
        $diff = $start->diff($end);
        $days = $diff->format('%a');
        // remove saturday and sundays
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);

        $nbHolidayDays = $this->getHolidayDaysForPeriod($start, $end);
        $days -= $nbHolidayDays;
        
        // remove saturday and sunday
        foreach ($period as $dt) {
            $curr = $dt->format('D');
            if ($curr == 'Sat' || $curr == 'Sun') {
                $days--;
            }
        }
        
        return $days;
    }

    public function getHolidayDaysForPeriod(\DateTime $start, \DateTime $end)
    {
        $holidays = $this->hrepository->findInRange($start, $end);
        $nbDays = 0;


        // remove days that are in holidays
        foreach ($holidays as $holiday) {
            $hstart = $holiday->getStart();
            $hend = $holiday->getEnd();

            if ($holiday->getStart() < $start) {
                $hstart = $start;
            }

            if ($holiday->getEnd() > $end) {
                $hend = $end;
            }

            if ($hstart->format('Y-m-d') === $hend->format('Y-m-d')) {
                $hend->modify('+1 day');
            }

            $hdiff = $hstart->diff($hend);
            $nbHolidayDays = $hdiff->format('%a');
            $nbDays += $nbHolidayDays;
        }
      
        return $nbDays;
    }
}

<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\HolidayRepository;
use App\Repository\EventRepository;

class StatsHelper
{
    protected $em;
    protected $hrepository;
    protected $erepository;

    public function __construct(
        EntityManagerInterface $em,
        HolidayRepository $hrepository,
        EventRepository $eventRepository
    ) {
        $this->em = $em;
        $this->hrepository = $hrepository;
        $this->erepository = $eventRepository;
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

        // the diff between two dates will give the number of days between those 2 dates so... we need to add one day
        $days += 1;
        
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
            // le DateTime::diff ne compte n'inclue pas les bornes... il faut donc ajouter un jour pour avoir le bon intervale
            //$hend->modify('+1 day');

            if ($holiday->getStart() < $start) {
                $hstart = $start;
            }

            if ($holiday->getEnd() > $end) {
                $hend = $end;
            }

            if ($hstart->format('Y-m-d') === $hend->format('Y-m-d')) {
                $hend->modify('+1 day');
            }

            // echo 'start ' . $hstart->format('Y-m-d H:i:s') . ' end ' . $hend->format('Y-m-d H:i:s');

            $hdiff = $hstart->diff($hend);
            $nbHolidayDays = $hdiff->format('%a');

            // echo 'nb holidays ' . $nbHolidayDays;
            $nbDays += $nbHolidayDays;
        }
      
        return $nbDays;
    }

    public function getTasksFromCatAndDates($dates, $task)
    {
        $events = [];
        foreach ($dates as $date) {
            $d = new \DateTime($date);
            $events[] = $this->erepository->getEventByDateAndTask($d, $task);
        }
        $start = new \DateTime($dates[0]);
        $end = new \DateTime($dates[count($dates) - 1]);
        $events[] = $this->erepository->getEventTotalByTask($task, $start, $end);

        return $events;
    }
}

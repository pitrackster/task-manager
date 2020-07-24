<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventRepository;

class EventManager
{
    protected $em;
    protected $repository;

    public function __construct(
        EntityManagerInterface $em,
        EventRepository $repository
    ) {
        $this->em = $em;
        $this->repository = $repository;
    }


    public function getDayAvailableTime(\DateTime $date)
    {
    }
}

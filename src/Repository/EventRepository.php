<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function getTimeLeftForADate($date)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(e.duration)
                FROM App\Entity\Event e
                WHERE e.date = :date'
        )
            ->setParameter('date', $date);

        return $query->getSingleScalarResult();
    }

    public function getEventByDateAndTask($date, $task)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(e.duration)
                FROM App\Entity\Event e
                WHERE e.date = :date 
                AND e.task = :task'
        )->setParameter('date', $date)->setParameter('task', $task);
        return $query->getSingleScalarResult();
    }

    public function getEventTotalByTask($task, $start, $end)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(e.duration)
                FROM App\Entity\Event e
                WHERE e.date <= :end
                AND e.date >= :start 
                AND e.task = :task'
        )
        ->setParameter('start', $start)
        ->setParameter('task', $task)
        ->setParameter('end', $end);
        return $query->getSingleScalarResult();
    }

    // /**
    //  * @return Event[] Returns an array of Event objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

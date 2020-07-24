<?php

namespace App\Repository;

use App\Entity\Holiday;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Holiday|null find($id, $lockMode = null, $lockVersion = null)
 * @method Holiday|null findOneBy(array $criteria, array $orderBy = null)
 * @method Holiday[]    findAll()
 * @method Holiday[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HolidayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Holiday::class);
    }

    /**
     * Find holidays existing in date range (only using end date)
     * @param [Date] $start
     * @param [Date] $end
     * @return void
     */
    public function findInRange($start, $end)
    {
        $qb = $this->createQueryBuilder('h')
             ->where('h.start BETWEEN :start AND :end')
             ->orWhere('h.end BETWEEN :start AND :end')
             ->setParameter('start', $start)
             ->setParameter('end', $end)
         ;
        return $qb->getQuery()->getResult();
    }

    public function findInRangeSQL($start, $end)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT h
                 FROM App\Entity\Holiday h
                 WHERE (h.start BETWEEN :start AND :end)
                 OR (h.end BETWEEN :start AND :end)'
        )
             ->setParameter('start', $start)
             ->setParameter('end', $end)
             ;

        return $query->getResult();
    }
}

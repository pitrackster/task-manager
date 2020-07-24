<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }


    public function findInDateRange($from, $to)
    {
        return $this->createQueryBuilder('c')
             ->leftJoin('c.tasks', 't')
             ->leftJoin('t.events', 'e')
             ->where('e.date >= :start')
             ->andWhere('e.date <= :end')
             ->orWhere('e.date IS NULL')
             ->setParameter('start', $from)
             ->setParameter('end', $to)
             ->orderBy('e.date', 'DESC')
             ->addOrderBy('e.date', 'DESC')
             ->getQuery()
             ->getResult();

        /* $entityManager = $this->getEntityManager();
         $query = $entityManager->createQuery(
             'SELECT c,t,e
                 FROM App\Entity\Category c
                 LEFT JOIN c.tasks t
                 LEFT JOIN t.events e
                 WHERE e.date >= :start AND e.date <= :end OR e.date IS NULL
                 ORDER BY e.date DESC'
         )
             ->setParameter('start', $from)
             ->setParameter('end', $to)
             ;

         return $query->getResult();*/
    }

    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\Show;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Show>
 *
 *
 * @method Show|null find($id, $lockMode = null, $lockVersion = null)
 * @method Show|null findOneBy(array $criteria, array $orderBy = null)
 * @method Show[]    findAll()
 * @method Show[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @ORM\HasLifecycleCallbacks()
 */
class ShowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Show::class);
    }

    public function save(Show $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Show $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllUpcomingShows() : array {
        $qb = $this->findAllUpcomingShowsQuery();

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Show[] Returns an array of Show objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Show
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findAllUpcomingShowsQuery(): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('s');
        $qb->where('s.date_start > :now')
            ->orderBy('s.date_start, s.id', 'ASC')
            ->setParameter('now', new \DateTime());
        return $qb;
    }

    public function findShowOverlappingWith(DateTime $startDate, DateTime $endDate): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.date_start <= :end')
            ->andWhere('s.date_end >= :start')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        return $qb->getQuery()->getResult();
    }

}

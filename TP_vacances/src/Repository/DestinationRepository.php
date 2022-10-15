<?php

namespace App\Repository;

use App\Entity\Destination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Destination>
 *
 * @method Destination|null find($id, $lockMode = null, $lockVersion = null)
 * @method Destination|null findOneBy(array $criteria, array $orderBy = null)
 * @method Destination[]    findAll()
 * @method Destination[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DestinationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Destination::class);
    }

    public function save(Destination $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Destination $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function maRequete($where): array
    {

        $queryBuilder = $this->createQueryBuilder("Destination");
        $queryBuilder->where(' Destination.name like :w');
        $queryBuilder->setParameter(':w', '%' . $where . '%');
        return $queryBuilder->getQuery()->getResult(); // on renvoie le résultat
    }

    /**
     * @throws NonUniqueResultException
     */
    public function moyenneReview(Destination $destination)
    {
        return $this->createQueryBuilder("destination")
            ->select("AVG(review.notation)")
            ->leftJoin('App\Entity\Review', 'review', Join::WITH, 'review.destination = destination.id')
            ->where("destination.id = :id")
            ->setParameter("id", $destination->getId())
            ->groupBy("destination")
            ->getQuery()
            ->getOneOrNullResult();


    }

//    /**
//     * @return Destination[] Returns an array of Destination objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Destination
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

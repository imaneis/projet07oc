<?php

namespace App\Repository;

use App\Entity\Consumer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Consumer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Consumer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Consumer[]    findAll()
 * @method Consumer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsumerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consumer::class);
    }

    public function findAllPaginated(int $page = 0, int $nbElementPerPage = 100, $name)
    {
        $q = $this->createQueryBuilder('b')
            ->where('b.clientName = :name')
            ->setParameter('name', $name)
            ->orderBy('b.id')
            ->setFirstResult($page * $nbElementPerPage)
            ->setMaxResults($nbElementPerPage);

        return new Paginator($q);
    }
}

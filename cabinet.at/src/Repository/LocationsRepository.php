<?php

namespace App\Repository;

use App\Entity\Locations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\DBAL\DoctrineFunctions\PostgresLtreeOperatorFunctionNode;

/**
 * @method Locations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Locations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Locations[]    findAll()
 * @method Locations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Locations::class);
    }

    /**
     * @param array $path
     * @param string $term
     * @return mixed
     */
    public function getQueryLocationsDataFromArray(array $path, string $term = null)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select(['l.title', 'l.path'])
            ->where(PostgresLtreeOperatorFunctionNode::FUNCTION_NAME . '(l.path, \'~\', :self_path) = true')
            ->setParameter('self_path', $path[0]);

        if (isset($path[1])) {
            $qb->orWhere(PostgresLtreeOperatorFunctionNode::FUNCTION_NAME . '(l.path, \'~\', :self_path2) = true')
                ->setParameter('self_path2', $path[1]);
        }

        if (isset($path[2])) {
            $qb->orWhere(PostgresLtreeOperatorFunctionNode::FUNCTION_NAME . '(l.path, \'~\', :self_path3) = true')
                ->setParameter('self_path3', $path[2]);
        }

        if (isset($path[3])) {
            $qb->orWhere(PostgresLtreeOperatorFunctionNode::FUNCTION_NAME . '(l.path, \'~\', :self_path4) = true')
                ->setParameter('self_path4', $path[3]);
        }

        if($term !== null) {
            $qb->andWhere($qb->expr()->like('l.title', ':term'))
                ->setParameter('term', $term);
        }

        return $qb->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @param int $id
     * @return string|null
     */
    public function getPathByDrvId(int $id): ?string
    {
        $em = $this->getEntityManager();
        $result = $em->createQuery("SELECT l.path FROM App\Entity\Locations l WHERE fetchval(l.options, 'temporary_drv_id')= :id")
            ->setParameter('id', $id)
            ->useQueryCache(true)
            ->getResult();

        return empty($result) === false ? $result[0]['path'] : null;
    }
}

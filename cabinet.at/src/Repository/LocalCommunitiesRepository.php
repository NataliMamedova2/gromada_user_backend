<?php

namespace App\Repository;

use App\Entity\LocalCommunities;
use App\Service\PathResolver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method LocalCommunities|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocalCommunities|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocalCommunities[]    findAll()
 * @method LocalCommunities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocalCommunitiesRepository extends ServiceEntityRepository
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var LocationsRepository */
    private $locationsRepository;

    /** @var PathResolver */
    private $pathResolver;

    /**
     * LocalCommunitiesRepository constructor.
     * @param ManagerRegistry $registry
     * @param EntityManagerInterface $em
     * @param LocationsRepository $locationsRepository
     * @param PathResolver $pathResolver
     */
    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $em,
        LocationsRepository $locationsRepository,
        PathResolver $pathResolver
    ) {
        parent::__construct($registry, LocalCommunities::class);
        $this->em = $em;
        $this->locationsRepository = $locationsRepository;
        $this->pathResolver = $pathResolver;
    }

    /**
     * @param string $term
     * @return array
     * @throws DBALException
     */
    public function getCommunitiesFromVoterRegistry(string $term): array
    {
        $term = \ucfirst(\trim($term));
        $sql = "SELECT dl.t7571 as id, dl.f3202 as title
               FROM eddr_lc.drv_lc dl WHERE
               dl.f3202 ILIKE :term
               ORDER BY f3202 ASC
               LIMIT 20";
        $execParams['term'] = '%' . $term . '%';

        $connection = $this->em->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($execParams);

        return $stmt->fetchAll();
    }

    /**
     * @param $id
     * @return array|null
     */
    public function getFullLocationById($id): ?array
    {
        $path = $this->locationsRepository->getPathByDrvId($id);
        $splitPath = $this->pathResolver->splitPath($path);
        $locations = $this->locationsRepository->getQueryLocationsDataFromArray($splitPath);
        $result = null;
        if ($locations !== null) {
            list($region, $city, $district) = $locations;
            $result = [
                'region' => $region,
                'city' => $city,
                'district' => $district
            ];
        }

        return $result;
    }

    /**
     * @param string $edrpou
     * @return mixed|null
     * @throws NonUniqueResultException
     */
    public function getByEdrpou(string $edrpou)
    {
        $localCommunity = $this->createQueryBuilder('l')
            ->andWhere('l.edrpou = :edrpou')
            ->setParameter('edrpou', $edrpou)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();

        if ($localCommunity === null) {
            return null;
        }

        if ($localCommunity->getStatus() === LocalCommunities::STATUS_LC_DELETED) {
            return null;
        }

        return $localCommunity;
    }

    /**
     * @param int $user
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getByRegistrationUser(int $user)
    {
       return $this->createQueryBuilder('l')
            ->where('l.registrationUser = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }
}

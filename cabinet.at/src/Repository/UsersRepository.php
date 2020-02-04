<?php

namespace App\Repository;

use App\CommandBus\Command\DependentUserActivate;
use App\CommandBus\Command\UserRegistry;
use App\Dto\UserCredentials;
use App\Dto\UsersLocalCommunity;
use App\Entity\LocalCommunities;
use App\Entity\Users;
use App\Pagination\Paginator;
use App\Service\UsersLocalCommunityResolver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /** @var EntityManagerInterface */
    private $em;

    private $resolver;

    /**
     * UsersRepository constructor.
     * @param ManagerRegistry $registry
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     * @param UsersLocalCommunityResolver $resolver
     */
    public function __construct(
        ManagerRegistry $registry,
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $em,
        UsersLocalCommunityResolver $resolver
    ) {
        parent::__construct($registry, Users::class);
        $this->encoder = $encoder;
        $this->em = $em;
        $this->resolver = $resolver;
    }

    /**
     * @param string $serial
     * @return bool
     * @throws NonUniqueResultException
     */
    public function checkCertificateForUniqueness(string $serial): bool
    {
        $qb = $this->createQueryBuilder('u');

        $user = $qb->where('u.certSerial =:serial')
            ->setParameter('serial', $serial)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user === null) {
            return true;
        }

        if ($user->getStatus() === Users::STATUS_USER_DELETED) {
            return true;
        }

        return false;
    }

    /**
     * @param UserCredentials $userCred
     * @return Users|null
     * @throws NonUniqueResultException
     */
    public function validateUserCredentials(UserCredentials $userCred): ?Users
    {
        $isValid = false;

        /** @var Users $user */
        $user = $this->createQueryBuilder('u')
            ->addSelect()
            ->andWhere('u.login = :login')
            ->andWhere('u.certSerial = :serial')
            ->andWhere('u.certIssuer =:issuer')
            ->andWhere('u.status =:status')
            ->setParameter('login', $userCred->login)
            ->setParameter('serial', $userCred->serial)
            ->setParameter('issuer', $userCred->issuer)
            ->setParameter('status', Users::STATUS_USER_ACTIVE)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($user !== null) {
            $isValid = $this->encoder->isPasswordValid($user, $userCred->password);
        }

        return $isValid === true ? $user : null;
    }

    /**
     * @param UserRegistry $userRegistry
     * @return Users|null
     * @throws NonUniqueResultException
     */
    public function getByUserRegistryParams(UserRegistry $userRegistry): ?Users
    {
        /** @var Users $user */
        $user = $this->createQueryBuilder('u')
            ->where('u.login = :login')
            ->orWhere('u.email = :email')
            ->orWhere('u.certSerial = :serial')
            ->setParameter('login', $userRegistry->userCredentials->login)
            ->setParameter('serial', $userRegistry->userCredentials->serial)
            ->setParameter('email', $userRegistry->email)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();

        if ($user === null) {
            return null;
        }

        if ($user->getStatus() === Users::STATUS_USER_DELETED && $user->getLogin() !== $userRegistry->userCredentials->login) {
            return null;
        }

        return $user;
    }

    /**
     * @param array $emails
     * @return array|null
     */
    public function getByEmails(array $emails): ?array
    {
        return $this->createQueryBuilder('u')
            ->addSelect('u.email')
            ->andWhere('u.email IN (:emails)')
            ->setParameter('emails', $emails, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @param string $regUrl
     * @return mixed
     */
    public function getByRegUrl(string $regUrl): array
    {
        return $this->em->createQuery("SELECT u FROM App\Entity\Users u WHERE fetchval(u.options, 'reg_url')= :regUrl AND u.status= :status")
            ->setParameter('regUrl', $regUrl)
            ->setParameter('status', Users::STATUS_USER_WAITING_FOR_ACTIVATION)
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @param DependentUserActivate $userActivate
     * @return DependentUserActivate|mixed|null
     * @throws NonUniqueResultException
     */
    public function getByUserActivateParams(DependentUserActivate $userActivate)
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.login = :login')
            ->orWhere('u.certSerial = :serial')
            ->setParameter('login', $userActivate->userCredentials->login)
            ->setParameter('serial', $userActivate->userCredentials->serial)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();

        if ($user === null) {
            return null;
        }

        if ($user->getStatus() === Users::STATUS_USER_DELETED && $user->getLogin() !== $userActivate->userCredentials->login) {
            return null;
        }

        return $user;
    }

    /**
     * @param string $login
     * @return Users|null
     * @throws NonUniqueResultException
     */
    public function getByLogin(string $login): ?Users
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->join(LocalCommunities::class, 'lc', 'with', 'lc.id = u.localCommunity')
            ->where('lc.status=' . LocalCommunities::STATUS_LC_ACTIVE)
            ->andWhere('u.login =:login')
            ->andWhere('u.status=' . Users::STATUS_USER_ACTIVE)
            ->setParameter('login', $login)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }

    /**
     * @param string $email
     * @return Users|null
     * @throws NonUniqueResultException
     */
    public function getByEmail(string $email): ?Users
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->join(LocalCommunities::class, 'lc', 'with', 'lc.id = u.localCommunity')
            ->where('lc.status=' . LocalCommunities::STATUS_LC_ACTIVE)
            ->andWhere('u.email =:email')
            ->andWhere('u.status=' . Users::STATUS_USER_ACTIVE)
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }

    /**
     * @param string $hash
     * @return mixed
     */
    public function getRestoreEmailOptionsByHash(string $hash)
    {
        return $this->em->createQuery("SELECT u FROM App\Entity\Users u WHERE fetchval(u.options, 'restore_email_url')= :hash                                      
                                     AND u.status= :status")
            ->setParameter('hash', $hash)
            ->setParameter('status', Users::STATUS_USER_INACTIVE)
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @param string $hash
     * @return mixed
     */
    public function getRestorePasswordOptionsByHash(string $hash)
    {
        return $this->em->createQuery("SELECT u FROM App\Entity\Users u WHERE fetchval(u.options, 'restore_password_url')= :hash                                      
                                     AND u.status= :status")
            ->setParameter('hash', $hash)
            ->setParameter('status', Users::STATUS_USER_INACTIVE)
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @param string $email
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function checkForUniqueByEmail(string $email): ?Users
    {
        $statuses = [
            Users::STATUS_USER_ACTIVE,
            Users::STATUS_USER_INACTIVE,
            Users::STATUS_USER_WAITING_FOR_ACTIVATION
        ];

        return $this->createQueryBuilder('u')
            ->where('u.email =:email')
            ->andWhere('u.status IN (:status)')
            ->setParameter('status', $statuses, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @param int $page
     * @return UsersLocalCommunity
     * @throws \Exception
     */
    public function getUsersByLocalCommunityId(int $id, int $page = 1)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.localCommunity =:id')
            ->setParameter('id', $id);

        $paginator = (new Paginator($qb))->paginate($page);

        return $this->resolver->normalizeResult($paginator);
    }

    /**
     * @inheritDoc
     */
    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.login = :login')
            ->setParameter('login', $username)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

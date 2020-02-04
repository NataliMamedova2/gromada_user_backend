<?php

namespace App\CommandBus\CommandHandler;

use App\CommandBus\Command\UserRegistry;
use App\Entity\LocalCommunities;
use App\Entity\Users;
use App\Events\NotificationInviteEmail;
use App\Repository\LocalCommunitiesRepository;
use App\Repository\UsersRepository;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserRegistryCommandHandler implements MessageHandlerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var LocalCommunitiesRepository */
    private $localCommunityRepository;

    /** @var UsersRepository */
    private $usersRepository;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ValidatorInterface */
    private $validator;

    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * UserRegistryCommandHandler constructor.
     * @param EntityManagerInterface $em
     * @param LocalCommunitiesRepository $localCommunitiesRepository
     * @param TranslatorInterface $translator
     * @param UsersRepository $usersRepository
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManagerInterface $em,
        LocalCommunitiesRepository $localCommunitiesRepository,
        TranslatorInterface $translator,
        UsersRepository $usersRepository,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->localCommunityRepository = $localCommunitiesRepository;
        $this->translator = $translator;
        $this->usersRepository = $usersRepository;
        $this->validator = $validator;
        $this->encoder = $encoder;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param UserRegistry $userRegistry
     * @throws ConnectionException
     */
    public function __invoke(UserRegistry $userRegistry)
    {
        $connection = $this->em->getConnection();
        $connection->beginTransaction();
        $connection->setAutoCommit(false);

        try {
            $this->validate($userRegistry);

            $localCommunity = new LocalCommunities();
            $localCommunity->setTitle($userRegistry->localCommunity->title);
            $localCommunity->setEdrpou($userRegistry->localCommunity->edrpou);
            $localCommunity->setLocation($userRegistry->localCommunity->location);
            $localCommunity->setStatus(LocalCommunities::STATUS_LC_WAITING_FOR_ACTIVATION);
            $localCommunity->setRegistrationUser(0);
            $contactInfo = ['email' => $userRegistry->email, 'phone' => $userRegistry->phone];
            $localCommunity->setContactInfo($contactInfo);

            $this->em->persist($localCommunity);

            $user = new Users();
            $user->setEmail($userRegistry->email);
            $user->setFirstname($userRegistry->firstName);
            $user->setLastname($userRegistry->lastName);
            $user->setMiddlename($userRegistry->middleName);
            $user->setLogin($userRegistry->userCredentials->login);
            $user->setLocalCommunity($localCommunity->getId());
            $user->setCertSerial($userRegistry->userCredentials->serial);
            $user->setCertIssuer($userRegistry->userCredentials->issuer);
            $user->setIsLocalCommunityHead(true);
            $user->setStatus(Users::STATUS_USER_WAITING_FOR_ACTIVATION);
            $user->setOptions(['reg_url' => $user->getRegUrl()]);
            $user->setPasswd($this->encoder->encodePassword($user, $userRegistry->userCredentials->password));
            $user->setPhone($userRegistry->phone);
            $user->setLastloginIp($userRegistry->lastLoginIp);

            $this->em->persist($user);

            $localCommunity->setRegistrationUser($user->getId());
            $this->em->persist($localCommunity);

            $this->em->flush();
            $connection->commit();

            $this->eventDispatcher->dispatch(new NotificationInviteEmail($user, $this->translator));
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param UserRegistry $userRegistry
     * @throws NonUniqueResultException
     */
    public function validate(UserRegistry $userRegistry): void
    {
        $violations = $this->validator->validate($userRegistry);

        if (count($violations) > 0) {
            throw new ValidatorException($violations->get(0)->getMessage());
        }

        $violations = $this->validator->validate($userRegistry->userCredentials);

        if (count($violations) > 0) {
            throw new ValidatorException($violations->get(0)->getMessage());
        }

        $violations = $this->validator->validate($userRegistry->localCommunity);

        if (count($violations) > 0) {
            throw new ValidatorException($violations->get(0)->getMessage());
        }

       if ($this->localCommunityRepository->getByEdrpou($userRegistry->localCommunity->edrpou) !== null) {
            throw new \Exception($this->translator->trans('cabinet.local_community.edrpou.exist'));
        }

        if ($this->usersRepository->getByUserRegistryParams($userRegistry) !== null) {
            throw new \Exception($this->translator->trans('cabinet.users.exist'));
        }
    }

    /**
     * @param UserRegistry $userRegistry
     * @param int $communityId
     * @throws \Exception
     */
    private function insertDependentUsers(UserRegistry $userRegistry, int $communityId): void
    {
       /* foreach ($userRegistry->dependentUsers as $dependentUser) {
            $user = new Users();
            $user->setEmail($dependentUser->email);
            $user->setFirstname($dependentUser->firstName);
            $user->setLastname($dependentUser->lastName);
            $user->setMiddlename($dependentUser->middleName);
            $user->setLogin(Users::generateTempLogin());
            $user->setLocalCommunity($communityId);
            $user->setCertSerial(Users::generateCertSerial());
            $user->setIsLocalCommunityHead(false);
            $user->setStatus(Users::STATUS_LC_WAITING_FOR_ACTIVATION);
            $user->setOptions(['reg_url' => $user->getRegUrl()]);
            $user->setPasswd($this->encoder->encodePassword($user, Users::generatePassword()));
            $user->setLastloginIp(Users::getLocalhostIp());

            $this->em->persist($user);
            $this->users[] = $user;
        }*/
    }
}
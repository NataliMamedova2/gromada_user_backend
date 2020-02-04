<?php

namespace App\CommandBus\CommandHandler;

use App\CommandBus\Command\UserAdminActivate;
use App\Entity\LocalCommunities;
use App\Entity\Users;
use App\Repository\LocalCommunitiesRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserAdminActivateCommandHandler implements MessageHandlerInterface
{
    /** @var UsersRepository */
    private $userRepository;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EntityManagerInterface */
    private $em;

    private $communityRepository;

    /**
     * UserAdminActivateCommandHandler constructor.
     * @param UsersRepository $userRepository
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $em
     * @param LocalCommunitiesRepository $communityRepository
     */
    public function __construct(
        UsersRepository $userRepository,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        LocalCommunitiesRepository $communityRepository
    ) {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
        $this->em = $em;
        $this->communityRepository = $communityRepository;
    }

    /**
     * @param UserAdminActivate $userActivate
     * @throws \Exception
     */
    public function __invoke(UserAdminActivate $userActivate)
    {
        $users = $this->userRepository->getByRegUrl($userActivate->hash);

        if (empty($users) === true) {
            throw new \Exception($this->translator->trans('cabinet.users.not.found'));
        }

        /** @var Users $user */
        $user = $users[0];

        if ($user->getRoles() !== ['ROLE_ADMIN']) {
            throw new \Exception($this->translator->trans('cabinet.users.not.found'));
        }

        /** @var LocalCommunities $community */
        $community = $this->communityRepository->getByRegistrationUser($user->getId());

        if ($community === null || $community->getStatus() !== LocalCommunities::STATUS_LC_WAITING_FOR_ACTIVATION) {
            throw new \Exception($this->translator->trans('cabinet.lc.not.found'));
        }

        $connection = $this->em->getConnection();

        try {

            $connection->beginTransaction();
            $connection->setAutoCommit(false);

            $user->setStatus(Users::STATUS_USER_ACTIVE);
            $user->setOptions(['reg_url' => '0']);

            $community->setStatus(LocalCommunities::STATUS_LC_ACTIVE);

            $this->em->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}

<?php

namespace App\CommandBus\CommandHandler;

use App\CommandBus\Command\DependentUserActivate;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DependentUserActivateCommandHandler implements MessageHandlerInterface
{
    /** @var UsersRepository */
    private $repository;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EntityManagerInterface */
    private $em;

    /** @var ValidatorInterface */
    private $validator;

    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /**
     * UserAdminActivateCommandHandler constructor.
     * @param UsersRepository $repository
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        UsersRepository $repository,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder
    ) {
        $this->repository = $repository;
        $this->translator = $translator;
        $this->em = $em;
        $this->validator = $validator;
        $this->encoder = $encoder;
    }

    /**
     * @param DependentUserActivate $userActivate
     * @throws \Exception
     */
    public function __invoke(DependentUserActivate $userActivate)
    {
        $users = $this->repository->getByRegUrl($userActivate->hash);

        if (empty($users) === true) {
            throw new \Exception($this->translator->trans('cabinet.users.not.found'));
        }

        /** @var Users $user */
        $user = $users[0];

        if ($user->getRoles() !== ['ROLE_USER']) {
            throw new \Exception($this->translator->trans('cabinet.users.not.found'));
        }

        $this->validate($userActivate);

        $user->setLogin($userActivate->userCredentials->login);
        $user->setCertIssuer($userActivate->userCredentials->issuer);
        $user->setCertSerial($userActivate->userCredentials->serial);
        $user->setPasswd($this->encoder->encodePassword($user, $userActivate->userCredentials->password));
        $user->setStatus(Users::STATUS_USER_ACTIVE);
        $user->setOptions(['reg_url' => '0']);

        $this->em->flush();
    }

    /**
     * @param DependentUserActivate $userActivate
     * @throws NonUniqueResultException
     */
    private function validate(DependentUserActivate $userActivate): void
    {
        $violations = $this->validator->validate($userActivate);

        if (count($violations) > 0) {
            throw new ValidatorException($violations->get(0)->getMessage());
        }

        $violations = $this->validator->validate($userActivate->userCredentials);

        if (count($violations) > 0) {
            throw new ValidatorException($violations->get(0)->getMessage());
        }

        if ($this->repository->getByUserActivateParams($userActivate) !== null) {
            throw new ValidatorException($this->translator->trans('cabinet.users.login.exist'));
        }
    }
}
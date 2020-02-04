<?php
declare(strict_types=1);

namespace App\CommandBus\CommandHandler;

use App\CommandBus\Command\UserChangePassword;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChangePasswordCommandHandler implements MessageHandlerInterface
{
    /** @var UsersRepository */
    private $repository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EntityManagerInterface */
    private $em;

    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /** @var EventDispatcherInterface  */
    private $eventDispatcher;

    /**
     * UserRequestRestoreEmailCommandHandler constructor.
     * @param UsersRepository $repository
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        UsersRepository $repository,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->em = $em;
        $this->encoder = $encoder;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param UserChangePassword $userParams
     * @throws \Exception
     */
    public function __invoke(UserChangePassword $userParams)
    {
        $violations = $this->validator->validate($userParams);

        if (count($violations) > 0) {
            throw new ValidatorException($violations->get(0)->getMessage());
        }
        /** @var Users $user */
        $user = $this->repository->getRestorePasswordOptionsByHash($userParams->hash);

        if (empty($user) === true) {
            throw new \Exception($this->translator->trans('cabinet.users.change.error.invalid.data'));
        }

        if ($user->getLogin() !== $userParams->login) {
            throw new \Exception($this->translator->trans('cabinet.users.change.error.invalid.data'));
        }

        $user->setOptions([
            'restore_password_url' => '0'
        ]);

        $user->setPasswd($this->encoder->encodePassword($user, $userParams->password));
        $user->setStatus(Users::STATUS_USER_ACTIVE);

        $this->em->flush();
    }
}
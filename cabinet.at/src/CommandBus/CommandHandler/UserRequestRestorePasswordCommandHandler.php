<?php
declare(strict_types=1);

namespace App\CommandBus\CommandHandler;

use App\CommandBus\Command\UserRequestRestorePassword;
use App\Entity\Users;
use App\Events\NotificationRestorePassword;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserRequestRestorePasswordCommandHandler implements MessageHandlerInterface
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

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * UserChangeEmailCommandHandler constructor.
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
     * @param UserRequestRestorePassword $userRequestRestorePassword
     * @throws NonUniqueResultException
     */
    public function __invoke(UserRequestRestorePassword $userRequestRestorePassword)
    {
        $violations = $this->validator->validate($userRequestRestorePassword);

        if (count($violations) > 0) {
            throw new ValidatorException($violations->get(0)->getMessage());
        }

        $user = $this->repository->getByEmail($userRequestRestorePassword->email);
        if ($user === null) {
            throw new \Exception($this->translator->trans('cabinet.users.not.found'));
        }

        $options = [
            'restore_' . Users::TYPE_CHANGING_PASSWORD . '_url' => $user->getRegUrl()
        ];

        $user->setOptions($options);
        $user->setStatus(Users::STATUS_USER_INACTIVE);

        $this->em->flush();

        $this->eventDispatcher->dispatch(new NotificationRestorePassword($user, $this->translator));
    }
}
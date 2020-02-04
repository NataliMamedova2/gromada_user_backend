<?php
declare(strict_types=1);

namespace App\Events;

use App\Entity\Users;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationRestorePassword extends AbstractNotificationEmail
{
    public function __construct(Users $user, TranslatorInterface $translator)
    {
        parent::__construct($user, $translator);

        $this->addLink = true;
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): string
    {
        return $this->translator->trans('cabinet.users.change.password.subject');
    }

    /**
     * @inheritDoc
     */
    public function getBodyH1(): string
    {
        return 'Ви надіслали запит на відновлення пароля у системі "Реєстр територіальних громад".';
    }

    /**
     * @inheritDoc
     */
    public function getBodyP(): string
    {
        return 'Для зміни пароля перейдіть за посиланням: '.$this->user->getLogin();
    }

    /**
     * @inheritDoc
     */
    public function getUrlPath(): string
    {
        return "/restore_password/" . $this->user->getOptions()['restore_password_url'];
    }

    /**
     * @inheritDoc
     */
    public function getTo(): string
    {
        return  $this->user->getEmail();
    }
}

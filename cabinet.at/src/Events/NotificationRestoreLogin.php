<?php
declare(strict_types=1);

namespace App\Events;

use App\Entity\Users;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationRestoreLogin extends AbstractNotificationEmail
{
    public function __construct(Users $user, TranslatorInterface $translator)
    {
        parent::__construct($user, $translator);

        $this->addLink = false;
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): string
    {
        return $this->translator->trans('cabinet.users.change.login.subject');
    }

    /**
     * @inheritDoc
     */
    public function getBodyH1(): string
    {
        return 'Ви надіслали запит на відновлення логіна у системі "Реєстр територіальних громад".';
    }

    /**
     * @inheritDoc
     */
    public function getBodyP(): string
    {
        return 'Нагадуємо Ваш логін '.$this->user->getLogin();
    }

    /**
     * @inheritDoc
     */
    public function getUrlPath(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getTo(): string
    {
        return  $this->user->getEmail();
    }
}
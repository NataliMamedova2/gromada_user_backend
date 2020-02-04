<?php
declare(strict_types=1);

namespace App\Events;

use App\Entity\Users;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationNewEmail extends AbstractNotificationEmail
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
        return $this->translator->trans('cabinet.users.change.email.subject');
    }

    /**
     * @inheritDoc
     */
    public function getBodyH1(): string
    {
        return $this->translator->trans('cabinet.users.change.email.h1');
    }

    /**
     * @inheritDoc
     */
    public function getBodyP(): string
    {
        return $this->translator->trans('cabinet.users.change.email.p', ['tag' => "</p>"]);
    }

    /**
     * @inheritDoc
     */
    public function getUrlPath(): string
    {
        return "/restore_email/" . $this->user->getOptions()['restore_email_url'];
    }

    /**
     * @inheritDoc
     */
    public function getTo(): string
    {
       return  $this->user->getOptions()['new_email'];
    }
}

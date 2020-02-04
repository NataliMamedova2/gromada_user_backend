<?php
declare(strict_types=1);

namespace App\Events;

class NotificationInviteEmail extends AbstractNotificationEmail
{
    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->translator->trans('cabinet.mail.invite.subject');
    }

    /**
     * @return string
     */
    public function getBodyH1(): string
    {
        return $this->translator->trans('cabinet.mail.head.invite.h1',
            ['name' => $this->user->getLastname() . ' ' . $this->user->getFirstname() . ' ' . $this->user->getMiddlename()]);

    }

    /**
     * @return string
     */
    public function getBodyP(): string
    {
        if ($this->user->getRoles() === ['ROLE_ADMIN']) {
            return $this->translator->trans('cabinet.mail.head.invite.p');
        }
        return $this->translator->trans('cabinet.mail.user.invite.p',
            ['tag' => '<br>']);
    }

    /**
     * @return string
     */
    public function getUrlPath(): string
    {
        if ($this->user->getRoles() === ['ROLE_ADMIN']) {
            return "/admin/confirm_email/" . $this->user->getOptions()['reg_url'];
        }

        return "/user/confirm_email/" . $this->user->getOptions()['reg_url'];
    }

    public function getTo(): string
    {
        return $this->user->getEmail();
    }
}
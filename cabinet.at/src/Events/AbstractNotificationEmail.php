<?php
declare(strict_types=1);

namespace App\Events;

use App\Entity\Users;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractNotificationEmail extends Event
{
    /**
     * @var Users
     */
    protected $user;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var bool  */
    public $addLink = true;

    /**
     * AdminNotification constructor.
     * @param Users $user
     * @param TranslatorInterface $translator
     */
    public function __construct(Users $user, TranslatorInterface $translator)
    {
        $this->user = $user;
        $this->translator = $translator;
    }

    /**
     * @return Users
     */
    public function getUser(): Users
    {
        return $this->user;
    }

    /**
     * @return string
     */
    abstract public function getSubject(): string;

    /**
     * @return string
     */
    abstract public function getBodyH1(): string;

    /**
     * @return string
     */
    abstract public function getBodyP(): string;

    /**
     * @return string
     */
    abstract public function getUrlPath(): string;

    /**
     * @return string
     */
    abstract public function getTo(): string ;

    /**
     * @return string
     */
    public function getFooterPart1(): string
    {
        return $this->translator->trans('cabinet.mail.invite.footer.part1');
    }

    /**
     * @return string
     */
    public function getFooterPart2(): string
    {
        return $this->translator->trans('cabinet.mail.invite.footer.part2');
    }

    /**
     * @return string
     */
    public function getFooterPart3(): string
    {
        return $this->translator->trans('cabinet.mail.invite.footer.part3');
    }
}
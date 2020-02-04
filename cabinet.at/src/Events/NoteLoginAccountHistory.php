<?php
declare(strict_types=1);

namespace App\Events;

use App\Entity\Users;
use Symfony\Contracts\EventDispatcher\Event;

class NoteLoginAccountHistory extends Event
{
    /** @var Users  */
    private $user;

    public function __construct(Users $user)
    {
        $this->user = $user;
    }

    /**
     * @return Users
     */
    public function getUser(): Users
    {
        return $this->user;
    }
}
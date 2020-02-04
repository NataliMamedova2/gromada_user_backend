<?php
declare(strict_types=1);

namespace App\CommandBus\Command;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;
use App\Validator\Constraints as EmailAssert;

class UserRequestRestoreLogin
{
    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.head.user.email")
     * @EmailAssert\ContainsDomainEmail()
     * @SWG\Property(type="string", property="email")
     * @Groups("user.restore.login")
     */
    public $email;
}

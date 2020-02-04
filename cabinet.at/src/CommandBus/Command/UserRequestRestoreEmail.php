<?php
declare(strict_types=1);

namespace App\CommandBus\Command;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;
use App\Validator\Constraints as EmailAssert;

class UserRequestRestoreEmail
{
     /**
     * @var string
     * @Assert\NotBlank(message="cabinet.head.user.email")
     * @EmailAssert\ContainsDomainEmail()
     * @SWG\Property(type="string", property="new_email")
     * @Groups("user.restore.email")
     */
    public $newEmail;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.login")
     * @Assert\Length(min = 2, minMessage="cabinet.validator.login.length")
     * @SWG\Property(type="string", property="login")
     * @Groups("user.restore.email")
     */
    public $login;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.password")
     * @Assert\Length(min = 9, minMessage="cabinet.validator.password.length")
     * @SWG\Property(type="string", property="password")
     * @Groups("user.restore.email")
     */
    public $password;
}

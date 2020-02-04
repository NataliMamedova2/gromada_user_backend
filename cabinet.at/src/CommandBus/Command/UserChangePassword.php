<?php
declare(strict_types=1);

namespace App\CommandBus\Command;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;
use App\Validator\Constraints as EmailAssert;

class UserChangePassword
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @SWG\Property(type="string", property="hash")
     * @Groups("user.change.password")
     */
    public $hash;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.password")
     * @Assert\Length(min = 9, minMessage="cabinet.validator.password.length")
     * @SWG\Property(type="string", property="password")
     * @Groups("user.restore.password")
     */
    public $password;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.login")
     * @Assert\Length(min = 2, minMessage="cabinet.validator.login.length")
     * @SWG\Property(type="string", property="login")
     * @Groups("user.password")
     */
    public $login;
}

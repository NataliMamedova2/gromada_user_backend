<?php
declare(strict_types=1);

namespace App\CommandBus\Command;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;


class UserAdminActivate
{
    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.user.hash")
     * @SWG\Property(type="string", property="hash")
     * @Groups("admin.activate")
     */
    public $hash;
}
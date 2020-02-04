<?php
declare(strict_types=1);

namespace App\CommandBus\Command;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;
use App\Validator\Constraints as EmailAssert;

class UserChangeEmail
{
     /**
     * @var string
     * @Assert\NotBlank()
     * @SWG\Property(type="string", property="hash")
     * @Groups("user.change.email")
     */
    public $hash;
}


<?php
declare(strict_types=1);

namespace App\CommandBus\Command;

use App\Dto\UserCredentials;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

class DependentUserActivate
{
    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.user.hash")
     * @SWG\Property(type="string", property="hash")
     * @Groups("user.activate")
     */
    public $hash;

    /**
     * @var UserCredentials
     * @Groups("user.login")
     * @SWG\Items(type="object",
     *   @SWG\Property(type="string", property="serial"),
     *   @SWG\Property(type="string", property="issuer"),
     *   @SWG\Property(type="string", property="login"),
     *   @SWG\Property(type="string", property="password")
     *  )
     */
    public $userCredentials;
}
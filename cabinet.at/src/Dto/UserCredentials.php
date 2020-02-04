<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

class UserCredentials
{
    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.serial")
     * @SWG\Property(type="string", property="serial")
     * @Groups("user.login")
     */
    public $serial;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.ussuer")
     * @SWG\Property(type="string", property="issuer")
     * @Groups("user.login")
     */
    public $issuer;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.login")
     * @Assert\Length(min = 2, minMessage="cabinet.validator.login.length")
     * @SWG\Property(type="string", property="login")
     * @Groups("user.login")
     */
    public $login;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.password")
     * @Assert\Length(min = 9, minMessage="cabinet.validator.password.length")
     * @SWG\Property(type="string", property="password")
     * @Groups("user.login")
     */
    public $password;
}
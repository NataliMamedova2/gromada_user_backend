<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;
use App\Validator\Constraints as EmailAssert;

class DependentUser
{
    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.dependent.users.firstName")
     * @SWG\Property(type="string", property="firstName")
     * @Groups("depend.user")
     *
     */
    public $firstName;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.dependent.users.lastName")
     * @SWG\Property(type="string", property="lastName")
     * @Groups("depend.user")
     * @var string
     */
    public $lastName;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.dependent.users.middleName")
     * @SWG\Property(type="string", property="middleName")
     * @Groups("depend.user")
     * @var string
     */
    public $middleName;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.dependent.users.email")
     * @EmailAssert\ContainsDomainEmail()
     * @SWG\Property(type="string", property="email")
     * @Groups("depend.user")
     */
    public $email;
}
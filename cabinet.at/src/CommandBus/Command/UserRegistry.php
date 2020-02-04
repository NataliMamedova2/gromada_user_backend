<?php
declare(strict_types=1);

namespace App\CommandBus\Command;

use App\Dto\LocalCommunity;
use App\Dto\UserCredentials;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;
use App\Validator\Constraints as EmailAssert;

class UserRegistry
{
    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.head.user.email")
     * @EmailAssert\ContainsDomainEmail()
     * @SWG\Property(type="string", property="email")
     * @Groups("head.user")
     */
    public $email;

    /**
     * @var string
     */
    public $lastLoginIp;

    /**
     * @var string|null
     * @SWG\Property(type="string", property="phone")
     * @Groups("head.user")
     */
    public $phone;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.head.user.firstName")
     * @SWG\Property(type="string", property="firstName")
     * @Groups("head.user")
     *
     */
    public $firstName;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.head.user.lastName")
     * @SWG\Property(type="string", property="lastName")
     * @Groups("head.user")
     * @var string
     */
    public $lastName;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.head.user.middleName")
     * @SWG\Property(type="string", property="middleName")
     * @Groups("head.user")
     */
    public $middleName;

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

    /**
     * @var LocalCommunity
     * @Groups("local.community")
     * @SWG\Items(type="object",
     *   @SWG\Property(type="string", property="title"),
     *   @SWG\Property(type="int", property="edrpou"),
     *   @SWG\Property(type="string", property="location")
     *  )
     */
    public $localCommunity;
}
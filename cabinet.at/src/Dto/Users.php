<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

class Users
{
    /**
     * @var int
     * @SWG\Property(type="integer", property="id")
     * @Groups("users.local.community")
     */
    public $id;

    /**
     * @var bool
     * @SWG\Property(type="boolean", property="is_admin")
     * @Groups("users.local.community")
     */
    public $isAdmin;

    /**
     * @var string
     * @SWG\Property(type="string", property="login")
     * @Groups("users.local.community")
     */
    public $login;

    /**
     * @var string
     * @SWG\Property(type="string", property="full_name")
     * @Groups("users.local.community")
     */
    public $fullName;

    /**
     * @var string
     * @SWG\Property(type="string", property="status")
     * @Groups("users.local.community")
     */
    public $status;
}
<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

class LocalCommunity
{
    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.community.title")
     * @SWG\Property(type="string", property="title")
     * @Groups("local.community")
     */
    public $title;

    /**
     * @var int|string
     * @Assert\NotNull(message="cabinet.validator.edrpou")
     * @SWG\Property(type="string", property="edrpou")
     * @Assert\Length(min = 8, minMessage="cabinet.validator.edrpou.length")
     * @Groups("local.community")
     */
    public $edrpou;

    /**
     * @var string
     * @Assert\NotBlank(message="cabinet.validator.community.location")
     * @SWG\Property(type="string", property="location")
     * @Groups("local.community")
     */
    public $location;
}
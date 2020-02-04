<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

class LocalCommunityDescription
{
    /**
     * @var string
     * @SWG\Property(type="string", property="id")
     * @Groups("local.community.description")
     */
    public $id;

    /**
     * @var string
     * @SWG\Property(type="string", property="title")
     * @Groups("local.community.description")
     */
    public $title;

    /**
     * @var string
     * @SWG\Property(type="string", property="edrpou")
     * @Groups("local.community.description")
     */
    public $edrpou;

    /**
     * @var string
     * @SWG\Property(type="string", property="status")
     * @Groups("local.community.description")
     */
    public $status;

    /**
     * @var string
     * @SWG\Property(type="string", property="registration_date")
     * @Groups("local.community.description")
     */
    public $registrationDate;

    /**
     * @var string
     * @SWG\Property(type="string", property="registration_place")
     * @Groups("local.community.description")
     */
   public $registrationPlace;

   /**
    * @var string
    * @SWG\Property(type="string", property="registration_user")
    * @Groups("local.community.description")
    */
   public $registrationUser;

   /**
    * @var  string|null
    * @SWG\Property(type="string", property="description")
    * @Groups("local.community.description")
    */
   public $description;

    /**
     * @var string
     * @SWG\Property(type="string", property="contact_phone")
     * @Groups("local.community.description")
     */
   public $contactPhone;

   /**
    * @var string
    * @SWG\Property(type="string", property="contact_email")
    * @Groups("local.community.description")
    */
   public $contactEmail;

   /**
    * @var string
    * @SWG\Property(type="string", property="legal_address")
    * @Groups("local.community.description")
    */
   public $legalAddress;
}
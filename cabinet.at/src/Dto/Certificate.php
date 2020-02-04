<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class Certificate
{
    /**
     * var string
     * @Assert\NotBlank()
     */
    public $serial;

    /**
     * var string
     * @Assert\NotBlank()
     */
    public $issuer;
}

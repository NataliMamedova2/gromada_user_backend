<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class Path
{
    /**
     * @var string
     * @Assert\NotBlank(message="term:not be blank")
     */
    public $term;

    /**
     * @var string
     * @Assert\NotBlank(message="path:not be blank")
     */
    public $path;

    /**
     * @var string
     * @Assert\NotBlank(message="level:not be blank")
     */
    public $level;

    /**
     * Path constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->term = $request->get('term');
        $this->path = $request->get('path');
        $this->level = $request->get('level');
    }
}
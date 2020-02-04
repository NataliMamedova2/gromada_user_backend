<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

class UsersLocalCommunity
{
    /**
     * @var int
     * @SWG\Property(type="integer", property="current_page")
     * @Groups("users.local.community")
     */
    public $currentPage;

    /**
     * @var int
     * @SWG\Property(type="integer", property="next_page")
     * @Groups("users.local.community")
     */
    public $nextPage;

    /**
     * @var int
     * @SWG\Property(type="integer", property="page_size")
     * @Groups("users.local.community")
     */
    public $pageSize;

    /**
     * @var int
     * @SWG\Property(type="integer", property="num_results")
     * @Groups("users.local.community")
     */
    public $numResults;

    /**
     * @var int
     * @SWG\Property(type="integer", property="previous_page")
     * @Groups("users.local.community")
     */
    public $previousPage;

    /**
     * @var Users[]
     * @SWG\Schema(type="array",
     *    @SWG\Items(type="object",
     *      @SWG\Property(type="int", property="id"),
     *      @SWG\Property(type="string", property="login"),
     *      @SWG\Property(type="string", property="full_name"),
     *      @SWG\Property(type="string", property="status"),
     *      @SWG\Property(type="bool", property="isAdmin")
     *    ),
     * )
     */
    public $results;
}
<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\Users;
use App\Dto\UsersLocalCommunity;
use App\Pagination\Paginator;
use Symfony\Contracts\Translation\TranslatorInterface;

class UsersLocalCommunityResolver
{
    /** @var TranslatorInterface  */
    private $translator;

    /**
     * UsersLocalCommunityResolver constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Paginator $paginator
     * @return UsersLocalCommunity
     */
    public function normalizeResult(Paginator $paginator): UsersLocalCommunity
    {
        $dto = new UsersLocalCommunity();
        foreach ($paginator->getResults() as $item){
            $users = new Users();
            $users->login = $item->getLogin();
            $users->fullName = $item->getLastname(). ' '. $item->getFirstname(). ' '.$item->getMiddlename();
            $users->status = $this->translator->trans('cabinet.user.status_'.$item->getStatus());
            $users->id = $item->getId();
            $users->isAdmin = $item->getIsLocalCommunityHead();
            $dto->results[] = $users;
        }

        $dto->currentPage = $paginator->getCurrentPage();
        $dto->nextPage = $paginator->getNextPage();
        $dto->pageSize = $paginator->getPageSize();
        $dto->numResults = $paginator->getNumResults();
        $dto->previousPage = $paginator->getPreviousPage();

        return $dto;
    }
}
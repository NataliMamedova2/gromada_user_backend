<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\LocalCommunityDescription;
use App\Entity\LocalCommunities;
use App\Entity\Users;
use App\Repository\LocationsRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocalCommunityDescriptionResolver
{
    /** @var LocalCommunities */
    private $localCommunity;

    /** @var TranslatorInterface */
    private $translator;

    /** @var Users */
    private $user;

    /** @var PathResolver */
    private $pathResolver;

    /** @var LocationsRepository */
    private $repository;

    /**
     * LocalCommunityDescriptionResolver constructor.
     * @param TranslatorInterface $translator
     * @param PathResolver $pathResolver
     * @param LocationsRepository $repository
     */
    public function __construct(
        TranslatorInterface $translator,
        PathResolver $pathResolver,
        LocationsRepository $repository
    ) {
        $this->translator = $translator;
        $this->pathResolver = $pathResolver;
        $this->repository = $repository;
    }

    /**
     * @param LocalCommunities $localCommunity
     */
    public function setLocalCommunity(LocalCommunities $localCommunity)
    {
        $this->localCommunity = $localCommunity;
    }

    /**
     * @param Users $user
     */
    public function setUser(Users $user)
    {
        $this->user = $user;
    }

    /**
     * @return LocalCommunityDescription|null
     */
    public function getLocalCommunityDescription(): ?LocalCommunityDescription
    {
        if ($this->localCommunity !== null) {
            $localCommunityDescription = new LocalCommunityDescription();

            $localCommunityDescription->edrpou = $this->localCommunity->getEdrpou();
            $localCommunityDescription->id = $this->localCommunity->getId();
            $localCommunityDescription->title = $this->localCommunity->getTitle();
            $localCommunityDescription->status = $this->translator->trans(
                'cabinet.user.local_community.status_' . $this->localCommunity->getStatus()
            );
            $localCommunityDescription->description = $this->localCommunity->getDescription();

            $localCommunityDescription->registrationUser = $this->user->getLastname() . ' '
                . $this->user->getFirstname() . ' '
                . $this->user->getMiddlename();

            $splitPath = $this->pathResolver->splitPath($this->localCommunity->getLocation());
            $localCommunityDescription->registrationPlace = $this->getRegistrationPlace($splitPath);
            $localCommunityDescription->registrationDate = $this->localCommunity->getRegistrationDate()->format('d.m.Y');
            $contactInfo = $this->localCommunity->getContactInfo();
            $localCommunityDescription->contactEmail = $contactInfo['email'] ?? null;
            $localCommunityDescription->contactPhone = $contactInfo['phone'] ?? null;
            $localCommunityDescription->legalAddress = $contactInfo['address'] ?? null;

            if($this->user->getIsLocalCommunityHead() === true){
                $localCommunityDescription->contactEmail = $this->user->getEmail();
            }

            return $localCommunityDescription;
        }

        return null;
    }

    /**
     * @param array $splitPath
     * @return string|null
     */
    private function getRegistrationPlace(array $splitPath): ?string
    {
        $registrationPlace = 'Україна';
        $locations = $this->repository->getQueryLocationsDataFromArray($splitPath);

        if (empty($locations) !== true && is_array($locations)) {
            foreach ($locations as $key => $item) {
                if($key === 0) {
                    $registrationPlace .= ' '.$item['title'].' обл.';
                }
                if($key === 1) {
                    $registrationPlace .= ' '.$item['title'].' р-н.';
                }
                if($key === 2) {
                    $registrationPlace .= ' '.$item['title'].' смт';
                }
            }
        }

        return $registrationPlace;
    }
}
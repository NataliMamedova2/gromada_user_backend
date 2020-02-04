<?php
declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Repository\LocalCommunitiesRepository;
use App\Repository\UsersRepository;
use App\Service\CacheDecorator;
use App\Service\LocalCommunityDescriptionResolver;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\Users;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocalCommunities extends AbstractController
{
    /** @var LocalCommunitiesRepository */
    private $repository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SerializerInterface */
    private $serializer;

    private $usersRepository;

    /**
     * Locations constructor.
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param LocalCommunitiesRepository $repository
     * @param UsersRepository $usersRepository
     */
    public function __construct(
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        LocalCommunitiesRepository $repository,
        UsersRepository $usersRepository
    ) {
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->repository = $repository;
        $this->usersRepository = $usersRepository;
    }

    /**
     * @Route("/local_communities", name="search_local_communities_by_term", methods = {"GET"})
     *
     * @Operation(
     *     description="Search local_communities by term from voter register",
     *     tags={"LocalCommunities"},
     *     summary="Search local_communities by term from voter register",
     *     @SWG\Parameter(
     *       name="term=K",
     *       in="query",
     *       type="string",
     *       description="parameters for filtering"
     *     ),
     *     @SWG\Response(
     *          description="Returned value",
     *          response="200",
     *         @SWG\Schema(
     *                type="array",
     *                title="response",
     *              	@SWG\Items(
     *                    type="object",
     *                  	@SWG\Property(property="id", type="string"),
     *                      @SWG\Property(property="title", type="string"),
     *                ),
     *         )
     *      )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function getLocalCommunitiesByTerm(Request $request): JsonResponse
    {
        $term = $request->get('term');
        if (empty($term) === true) {
            throw new ValidatorException('term params not be blank');
        }
        try {

            return $this->json($this->repository->getCommunitiesFromVoterRegistry($term), Response::HTTP_OK);

        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/local_communities/{id}/location", name="get_local_communities_location", methods = {"GET"})
     *
     * @Operation(
     *     description="Get local_community full location by id",
     *     tags={"LocalCommunities"},
     *     summary="Get local_community full location by id",
     *
     *     @SWG\Response(
     *          description="Returned value",
     *          response="200",
     *         @SWG\Schema(
     *                type="array",
     *                title="response",
     *              	@SWG\Items(
     *                    type="object",
     *                  	@SWG\Property(property="title", type="string"),
     *                      @SWG\Property(property="path", type="string")
     *                ),
     *         )
     *      )
     * )
     * @param int $id
     * @return JsonResponse
     */
    public function getLocalCommunitiesLocation(int $id): JsonResponse
    {
        try {
            if ($id === null) {
                throw new ValidatorException('id params not be null value');
            }

            return $this->json($this->repository->getFullLocationById($id), Response::HTTP_OK);

        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/communities/user_community", name="get_community_by_user", methods = {"GET"})
     *
     * @Operation(
     *     description="Get local_community description by user, who to get from jwt token",
     *     tags={"LocalCommunities"},
     *     summary="Get local_community description by user, who to get from jwt token",
     *
     *     @SWG\Response(
     *          description="Returned value",
     *          response="200",
     *          @SWG\Schema(ref=@Model(type="App\Dto\LocalCommunityDescription"))
     *      )
     * )
     * @param TranslatorInterface $translator
     * @param CacheDecorator $cache
     * @param LocalCommunityDescriptionResolver $resolver
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function getUserLocalCommunityDescription(
        TranslatorInterface $translator,
        CacheDecorator $cache,
        LocalCommunityDescriptionResolver $resolver
    ): JsonResponse {
        try {
            $user = $this->getUser();
            $localCommunityId = $user->getLocalCommunity();

            $localCommunity = $this->repository->find($localCommunityId);

            if ($localCommunity === null) {
                throw new \Exception($translator->trans('cabinet.community.not.found'));
            }

            $cacheData = $cache->getCachedData("$localCommunityId");
            if ($cacheData === null) {
                $resolver->setLocalCommunity($localCommunity);
                $resolver->setUser($user);

                $cacheData = $resolver->getLocalCommunityDescription();
                $cache->saveDataToCache("$localCommunityId", $cacheData);
            }

        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($cacheData, Response::HTTP_OK);
    }

    /**
     * @Route("/communities/{id}/users",  defaults={"page":"1"}, name="get_users_by_local_communtiy_id", methods = {"GET"})
     * @Route("/communities/{id}/users/page/{page<[1-9]\d*>}", name="users_list_paginated",  methods={"GET"})
     * @Operation(
     *     description="Get users from local communtiy",
     *     tags={"LocalCommunities"},
     *     summary="Get users from local communtiy",
     *
     *     @SWG\Response(
     *          description="Returned value",
     *          response="200",
     *          @SWG\Schema(ref=@Model(type="App\Dto\UsersLocalCommunity"))
     *      )
     * )
     * @param int $id
     * @param int $page
     * @return JsonResponse
     */
    public function getLocationCommunityUsers(int $id, int $page): JsonResponse
    {
        try {
            $users = $this->usersRepository->getUsersByLocalCommunityId($id, $page);
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json($users, Response::HTTP_OK);
    }
}
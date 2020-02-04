<?php
declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Dto\Path;
use App\Repository\LocationsRepository;
use App\Service\PathResolver;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Locations extends AbstractController
{
    /** @var LocationsRepository  */
    private $repository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * Locations constructor.
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param LocationsRepository $repository
     */
    public function __construct(ValidatorInterface $validator, SerializerInterface $serializer, LocationsRepository $repository)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->repository = $repository;
    }

    /**
     * @Route("/locations", name="get_location_by_filter", methods = {"GET"})
     *
     * @Operation(
     *     description="Get locations from query ",
     *     tags={"Locations"},
     *     summary="Get locations from query",
     *     @SWG\Parameter(
     *       name="term=K&path=1.2&level=3",
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
     *                  	@SWG\Property(property="path", type="string"),
     *                      @SWG\Property(property="title", type="string"),
     *                ),
     *         )
     *      )
     * )
     * @param Request $request
     * @param PathResolver $pathResolver
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function getLocation(Request $request, PathResolver $pathResolver): JsonResponse
    {
        try {
            $path = new Path($request);
            $violations = $this->validator->validate($path);

            if (count($violations) > 0) {
                throw new ValidatorException($violations->get(0)->getMessage());
            }

            $result = $pathResolver->getData($path);

            return $this->json($result, Response::HTTP_OK);
        }catch (\Throwable $exception){
            return $this->json(['errors' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
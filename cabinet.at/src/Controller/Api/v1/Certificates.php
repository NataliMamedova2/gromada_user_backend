<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class Certificates extends AbstractController
{
    /** @var UsersRepository */
    private $repository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(
        UsersRepository $repository,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/certificates/{hash}/unique", name="check_inique_certificate", methods = {"GET"})
     *
     * @Operation(
     *     description="Сheck certificate for uniqueness",
     *     tags={"Certificates"},
     *     summary="Сheck certificate for uniqueness",
     *     @SWG\Response(
     *          description="Returned value",
     *          response="200",
     *         @SWG\Schema(
     *                type="array",
     *              	@SWG\Items(
     *                    type="object",
     *                  	@SWG\Property(property="error", type="string"),
     *                  	@SWG\Property(property="result", type="boolean"),
     *                ),
     *         )
     *   )
     * )
     *
     * @param string $hash
     * @return JsonResponse
     */
    public function checkUniqueCertificate(string $hash): JsonResponse
    {
        $serial = \trim(\base64_decode($hash));
        try {
            $result = $this->repository->checkCertificateForUniqueness($serial);

            return $this->json(['result' => $result], Response::HTTP_OK);
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }

        return $this->json(['error' => $error], Response::HTTP_BAD_REQUEST);
    }
}
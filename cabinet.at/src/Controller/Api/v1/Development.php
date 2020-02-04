<?php
declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Repository\LocalCommunitiesRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;


class Development extends AbstractController
{
    private $communityRepository;

    private $userRepository;

    public function __construct(UsersRepository $usersRepository, LocalCommunitiesRepository $localCommunitiesRepository)
    {
        $this->communityRepository = $localCommunitiesRepository;
        $this->userRepository = $usersRepository;
    }

    /**
     * @Route("/development/users/{login}/community/{edrpou}", name="delete_users", methods = {"DELETE"})
     * @param string $login
     * @param string $edrpou
     * @return Json
     */
    public function deleteTestData(string $login, string $edrpou): JsonResponse
    {
        $user = $this->userRepository->findBy(['login' => $login]);
        $communtiy = $this->communityRepository->findBy(['edrpou' => $edrpou]);
        if(empty($user) === false) {
            $this->getDoctrine()->getManager()->remove($user[0]);
        }
        if(empty($communtiy) === false) {
            $this->getDoctrine()->getManager()->remove($communtiy[0]);
        }
        $this->getDoctrine()->getManager()->flush();
        return $this->json([], Response::HTTP_OK);
    }
}
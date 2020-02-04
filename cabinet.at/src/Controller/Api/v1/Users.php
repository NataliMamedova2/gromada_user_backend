<?php
declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\CommandBus\Command\DependentUserActivate;
use App\CommandBus\Command\UserAdminActivate;
use App\CommandBus\Command\UserChangeEmail;
use App\CommandBus\Command\UserChangePassword;
use App\CommandBus\Command\UserRequestRestoreEmail;
use App\CommandBus\Command\UserRegistry;
use App\CommandBus\Command\UserRequestRestorePassword;
use App\CommandBus\CommandHandler\DependentUserActivateCommandHandler;
use App\CommandBus\CommandHandler\UserAdminActivateCommandHandler;
use App\CommandBus\CommandHandler\UserChangeEmailCommandHandler;
use App\CommandBus\CommandHandler\UserChangePasswordCommandHandler;
use App\CommandBus\CommandHandler\UserRequestRestoreEmailCommandHandler;
use App\CommandBus\CommandHandler\UserRegistryCommandHandler;
use App\CommandBus\CommandHandler\UserRequestRestorePasswordCommandHandler;
use App\Events\NoteLoginAccountHistory;
use App\Repository\LocalCommunitiesRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Dto\UserCredentials;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\LocalCommunities;
use Symfony\Contracts\Translation\TranslatorInterface;

class Users extends AbstractController
{
    /** @var UsersRepository */
    private $repository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SerializerInterface */
    private $serializer;

    /** @var LocalCommunitiesRepository */
    private $communityRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        UsersRepository $repository,
        ValidatorInterface $validator,
        LocalCommunitiesRepository $communityRepository,
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->communityRepository = $communityRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/users/login", name="get_user", methods = {"POST"})
     *
     * @Operation(
     *     description="Login user and issuer access token",
     *     tags={"Users"},
     *     summary="Login user and issuer access token",
     *     @SWG\Response(
     *          description="Returned value",
     *          response="200",
     *         @SWG\Schema(
     *                type="array",
     *              	@SWG\Items(
     *                    type="object",
     *                  	@SWG\Property(property="error", type="string"),
     *                  	@SWG\Property(property="token", type="string"),
     *                ),
     *         )
     *      ),
     *      @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          @SWG\Schema(ref=@Model(type="App\Dto\UserCredentials", groups={"user.login"}))
     *     )
     * )
     *
     * @param Request $request
     *
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    public function login(
        Request $request,
        JWTTokenManagerInterface $JWTTokenManager,
        TranslatorInterface $translator
    ): JsonResponse {
        try {
            $userCredentials = $this->serializer->deserialize($request->getContent(), UserCredentials::class, 'json');
            $violations = $this->validator->validate($userCredentials);

            if (count($violations) > 0) {
                throw new ValidatorException($violations->get(0)->getMessage());
            }

            $user = $this->repository->validateUserCredentials($userCredentials);

            if ($user === null) {
                throw new \Exception($translator->trans('cabinet.users.notfound'));
            }

            $community = $this->communityRepository->find($user->getLocalCommunity());
            if ($community === null) {
                throw new \Exception($translator->trans('cabinet.lc.not.found'));
            }

            if ($community->getStatus() !== LocalCommunities::STATUS_LC_ACTIVE) {
                throw new \Exception($translator->trans('cabinet.lc.not.active'));
            }
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->eventDispatcher->dispatch(new NoteLoginAccountHistory($user));
        return $this->json(['token' => $JWTTokenManager->create($user)], Response::HTTP_OK);
    }

    /**
     * @Route("/users/registry", name="add_user", methods = {"POST"})
     *
     * @Operation(
     *     description="Registry head user with assignee staff and local community",
     *     tags={"Users"},
     *     summary="Registry head user with assignee staff and local community",
     *     @SWG\Response(
     *          description="Head user was created",
     *          response="200",
     *          @SWG\Schema(
     *             @SWG\Property(property="success_message", type="string")
     *         )
     *      ),
     *     @SWG\Response(
     *          description="Bad request",
     *          response="400",
     *         @SWG\Schema(
     *             @SWG\Property(property="error", type="string")
     *         )
     *      ),
     *      @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          @SWG\Schema(ref=@Model(type="App\CommandBus\Command\UserRegistry", groups={"head.user", "local.community", "user.login"}))
     *     )
     * )
     *
     * @param Request $request
     *
     * @param TranslatorInterface $translator
     * @param UserRegistryCommandHandler $commandHandler
     * @return JsonResponse
     */
    public function register(
        Request $request,
        TranslatorInterface $translator,
        UserRegistryCommandHandler $commandHandler
    ): JsonResponse {
        try {
            $userRegistry = $this->serializer->deserialize($request->getContent(), UserRegistry::class, 'json',
                ['groups' => ['head.user', 'user.login', 'local.community']]);
            $userRegistry->lastLoginIp = $request->getClientIp();

            $commandHandler($userRegistry);
            $successMessage = $translator->trans('cabinet.user.registration.final', ['email' => $userRegistry->email]);
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['success_message' => $successMessage], Response::HTTP_OK);
    }

    /**
     * @Route("/users/admin/activate", name="user_admin_activate", methods={"PATCH"})
     *
     * @Operation(
     *     description="Activate user with role ADMIN",
     *     tags={"Users"},
     *     summary="Activate user with role ADMIN",
     *     @SWG\Response(
     *          description="User was updated",
     *          response="204"
     *      ),
     *     @SWG\Response(
     *          description="Bad request",
     *          response="400",
     *         @SWG\Schema(
     *             @SWG\Property(property="error", type="string")
     *         )
     *      ),
     *      @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          @SWG\Schema(ref=@Model(type="App\CommandBus\Command\UserAdminActivate", groups={"admin.activate"}))
     *     )
     * )
     * @param Request $request
     * @param UserAdminActivateCommandHandler $commandHandler
     * @return JsonResponse
     */
    public function confirmAdminUserByEmail(
        Request $request,
        UserAdminActivateCommandHandler $commandHandler
    ): JsonResponse {
        try {
            $userActivate = $this->serializer->deserialize($request->getContent(), UserAdminActivate::class, 'json');
            $commandHandler($userActivate);
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/users/user/activate", name="user_activate", methods={"PATCH"})
     *
     * @Operation(
     *     description="Activate user with role USER",
     *     tags={"Users"},
     *     summary="Activate user with role USER",
     *     @SWG\Response(
     *          description="User was updated",
     *          response="204"
     *      ),
     *     @SWG\Response(
     *          description="Bad request",
     *          response="400",
     *         @SWG\Schema(
     *             @SWG\Property(property="error", type="string")
     *         )
     *      ),
     *      @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          @SWG\Schema(ref=@Model(type="App\CommandBus\Command\DependentUserActivate", groups={"user.activate", "user.login"}))
     *     )
     * )
     * @param Request $request
     * @param DependentUserActivateCommandHandler $commandHandler
     * @return JsonResponse
     */
    public function confirmUserByEmail(
        Request $request,
        DependentUserActivateCommandHandler $commandHandler
    ): JsonResponse {
        try {
            $userRegistry = $this->serializer->deserialize($request->getContent(), DependentUserActivate::class, 'json',
                ['groups' => ['user.activate', 'user.login']]);
            $commandHandler($userRegistry);
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/users/logout", name="app_logout", methods={"GET"})
     *
     * @Operation(
     *     description="User logout",
     *     tags={"Users"},
     *     summary="User logout",
     *     @SWG\Response(
     *          description="User logout",
     *          response="200"
     *      )
     * )
     *
     * @param string $login
     * @return JsonResponse
     */
    public function logout(string $login): JsonResponse
    {
        ;


        return $this->json([], Response::HTTP_OK);

    }

    /**
     * @Route("/users/user/request_restore_email", name="user_request_restore_email", methods={"POST"})
     *
     * @Operation(
     *     description="User's request for restore email",
     *     tags={"Users"},
     *     summary="User's request for restore email",
     *     @SWG\Response(
     *          description="Success",
     *          response="200",
     *        @SWG\Schema(
     *             @SWG\Property(property="success_message", type="string")
     *         )
     *      ),
     *     @SWG\Response(
     *          description="Bad request",
     *          response="400",
     *         @SWG\Schema(
     *             @SWG\Property(property="error", type="string")
     *         )
     *      ),
     *      @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          @SWG\Schema(ref=@Model(type="App\CommandBus\Command\UserRequestRestoreEmail", groups={"user.restore.email"}))
     *     )
     * )
     * @param Request $request
     * @param UserRequestRestoreEmailCommandHandler $commandHandler
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    public function requestRestoreEmail(
        Request $request,
        UserRequestRestoreEmailCommandHandler $commandHandler,
        TranslatorInterface $translator
    ): JsonResponse {
        try {
            $userRestoreEmail = $this->serializer->deserialize($request->getContent(), UserRequestRestoreEmail::class,
                'json');
            $commandHandler($userRestoreEmail);

        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['success_message' => $translator->trans('cabinet.users.check.email.message')],
            Response::HTTP_OK);
    }

    /**
     * @Route("/users/user/request_restore_login", name="user_request_restore_login", methods={"POST"})
     *
     * @Operation(
     *     description="User's request for restore login",
     *     tags={"Users"},
     *     summary="User's request for restore login",
     *     @SWG\Response(
     *          description="Success",
     *          response="200",
     *        @SWG\Schema(
     *             @SWG\Property(property="success_message", type="string")
     *         )
     *      ),
     *     @SWG\Response(
     *          description="Bad request",
     *          response="400",
     *         @SWG\Schema(
     *             @SWG\Property(property="error", type="string")
     *         )
     *      ),
     *      @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          @SWG\Schema(ref=@Model(type="App\CommandBus\Command\UserRequestRestoreLogin", groups={"user.restore.login"}))
     *     )
     * )
     * @param Request $request
     * @param UserRequestRestoreEmailCommandHandler $commandHandler
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    public function requestRestoreLogin(
        Request $request,
        UserRequestRestoreEmailCommandHandler $commandHandler,
        TranslatorInterface $translator
    ): JsonResponse {
        try {
            $userRestoreEmail = $this->serializer->deserialize($request->getContent(), UserRequestRestoreEmail::class,
                'json');
            $commandHandler($userRestoreEmail);

        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['success_message' => $translator->trans('cabinet.users.check.email.message.restore.login')],
            Response::HTTP_OK);
    }

    /**
     * @Route("/users/user/request_restore_password", name="user_request_restore_password", methods={"POST"})
     *
     * @Operation(
     *     description="User's request for restore password",
     *     tags={"Users"},
     *     summary="User's request for restore password",
     *     @SWG\Response(
     *          description="Success",
     *          response="200",
     *        @SWG\Schema(
     *             @SWG\Property(property="success_message", type="string")
     *         )
     *      ),
     *     @SWG\Response(
     *          description="Bad request",
     *          response="400",
     *         @SWG\Schema(
     *             @SWG\Property(property="error", type="string")
     *         )
     *      ),
     *      @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          @SWG\Schema(ref=@Model(type="App\CommandBus\Command\UserRequestRestorePassword", groups={"user.restore.password"}))
     *     )
     * )
     * @param Request $request
     * @param UserRequestRestorePasswordCommandHandler $commandHandler
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    public function requestRestorePassword(
        Request $request,
        UserRequestRestorePasswordCommandHandler $commandHandler,
        TranslatorInterface $translator
    ): JsonResponse {
        try {
            $userRestorePassword = $this->serializer->deserialize($request->getContent(),
                UserRequestRestorePassword::class, 'json');
            $commandHandler($userRestorePassword);
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['success_message' => $translator->trans('cabinet.users.check.email.message.restore.login')],
            Response::HTTP_OK);
    }

    /**
     * @Route("/users/user/restore_email", name="user_restore_email", methods={"PATCH"})
     *
     * @Operation(
     *     description="Change user's email",
     *     tags={"Users"},
     *     summary="Change user's email",
     *     @SWG\Response(
     *          description="Success",
     *          response="200",
     *        @SWG\Schema(
     *             @SWG\Property(property="success_message", type="string")
     *         )
     *      ),
     *     @SWG\Response(
     *          description="Bad request",
     *          response="400",
     *         @SWG\Schema(
     *             @SWG\Property(property="error", type="string")
     *         )
     *      ),
     *      @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          @SWG\Schema(ref=@Model(type="App\CommandBus\Command\UserChangeEmail", groups={"user.change.email"}))
     *     )
     * )
     * @param Request $request
     * @param UserChangeEmailCommandHandler $commandHandler
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    public function changeUserEmail(
        Request $request,
        UserChangeEmailCommandHandler $commandHandler,
        TranslatorInterface $translator
    ): JsonResponse {
        try {
            $userChangeEmail = $this->serializer->deserialize($request->getContent(), UserChangeEmail::class, 'json');
            $commandHandler($userChangeEmail);

        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['success_message' => $translator->trans('cabinet.users.restore.email.success')],
            Response::HTTP_OK);
    }

    /**
     * @Route("/users/user/restore_password", name="user_restore_password", methods={"PATCH"})
     *
     * @Operation(
     *     description="Change user's password",
     *     tags={"Users"},
     *     summary="Change user's password",
     *     @SWG\Response(
     *          description="Success",
     *          response="200",
     *        @SWG\Schema(
     *             @SWG\Property(property="success_message", type="string")
     *         )
     *      ),
     *     @SWG\Response(
     *          description="Bad request",
     *          response="400",
     *         @SWG\Schema(
     *             @SWG\Property(property="error", type="string")
     *         )
     *      ),
     *      @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          @SWG\Schema(ref=@Model(type="App\CommandBus\Command\UserChangePassword", groups={"user.change.password"}))
     *     )
     * )
     * @param Request $request
     * @param UserChangePasswordCommandHandler $commandHandler
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    public function changeUserPassword(
        Request $request,
        UserChangePasswordCommandHandler $commandHandler,
        TranslatorInterface $translator
    ): JsonResponse {
        try {
            $userChangePassword = $this->serializer->deserialize($request->getContent(), UserChangePassword::class,
                'json');
            $commandHandler($userChangePassword);

        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['success_message' => $translator->trans('cabinet.users.restore.password.success')],
            Response::HTTP_OK);
    }
}
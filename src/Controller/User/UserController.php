<?php

namespace App\Controller\User;

use App\Command\User\CreateUserCommand;
use App\Controller\ApiController;
use App\Domain\User\Validation\UserRegisterValidator;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends ApiController
{
    #[Route('/api/auth/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        UserRegisterValidator $validator,
        CreateUserCommand $createUserCommand,
        UserRepository $userRepository
    ): Response {
        $payload = $this->getRequestPayload($request);
        $violations = $validator->validate($payload);
        if ($violations->count()) {
            return $this->checkViolations($violations);
        }

        if ($userRepository->findOneBy(['email' => $payload['email']])) {
           return $this->errorResponse(
               message:'Email already exists',
               statusCode: Response::HTTP_BAD_REQUEST
           );
        }

        try {
            ($createUserCommand)($payload);
        } catch (\Exception $exception) {
            return $this->errorResponse(
                errors: [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'trace' => $exception->getTraceAsString(),
                ]
            );
        }

        return $this->successResponse(
            message:'User created successfully',
            statusCode: Response::HTTP_CREATED
        );
    }

    #[Route('/api/auth/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRegisterValidator $validator,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        JWTTokenManagerInterface $jwtManager,
        ParameterBagInterface $parameterBag
    ): Response {
        $payload = $this->getRequestPayload($request);
        $violations = $validator->validate($payload);
        if ($violations->count()) {
            return $this->checkViolations($violations);
        }

        $user = $userRepository->findOneBy(['email' => $payload['email']]);
        if (!$user) {
            return $this->errorResponse(
                message: 'Invalid credentials',
                statusCode: Response::HTTP_BAD_REQUEST
            );
        }

        if (!$passwordHasher->isPasswordValid($user, $payload['password'])) {
            return $this->errorResponse(
                message: 'Invalid credentials',
                statusCode: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->successResponse(
            data: [
                'token' => $jwtManager->create($user),
                'expiresIn' => $parameterBag->get('lexik_jwt_authentication.token_ttl'),
            ],
        );
    }
}

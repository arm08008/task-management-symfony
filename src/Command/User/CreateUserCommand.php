<?php

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CreateUserCommand
{
    /**
     * @param UserRepository $repository
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(
        protected UserRepository $repository,
        protected UserPasswordHasherInterface $hasher,
    )
    {
    }

    /**
     * @param array $payload
     *
     * @return UserInterface
     */
    public function __invoke(array $payload): UserInterface
    {
        $user = new User();
        $user->setEmail($payload['email']);
        $user->setPassword(
            $this->hasher->hashPassword($user, $payload['password'])
        );
        $user->setRoles(['ROLE_USER']);

        $this->repository->save($user, true);

        return $user;
    }
}
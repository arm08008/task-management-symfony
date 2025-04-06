<?php

namespace App\Domain\User\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRegisterValidator
{
    private Constraint|array|null $constraints;

    public function __construct(private readonly ValidatorInterface $validator)
    {
        $this->constraints = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(),
                new Assert\NotNull(),
                new Assert\Type(['string']),
                new Assert\Email(),
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Type(['string']),
            ]
        ]);
    }

    public function validate(array $data = []): ConstraintViolationListInterface
    {
       return $this->validator->validate($data, $this->constraints);
    }
}
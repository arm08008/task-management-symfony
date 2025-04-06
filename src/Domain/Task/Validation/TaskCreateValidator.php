<?php

namespace App\Domain\Task\Validation;

use App\Domain\Task\TaskStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


class TaskCreateValidator
{
    private Constraint|array|null $constraints;

    public function __construct(private readonly ValidatorInterface $validator)
    {
        $this->constraints = new Assert\Collection([
            'title' => [
                new Assert\NotBlank(),
                new Assert\NotNull(),
                new Assert\Type(['string'])
            ],
            'description' => [
                new Assert\Type(['string']),
            ],
            'status' => [
                new Assert\Choice(array_map(fn(TaskStatusEnum $e) => $e->value, TaskStatusEnum::cases())),
            ],
            'dueDate' => [
                new Assert\NotBlank(),
                new Assert\DateTime(['format' => 'Y-m-d']),
            ]
        ]);
    }

    public function validate(array $data = []):ConstraintViolationListInterface
    {
        return $this->validator->validate($data, $this->constraints);
    }
}
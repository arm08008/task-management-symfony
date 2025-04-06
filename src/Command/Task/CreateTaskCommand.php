<?php

namespace App\Command\Task;

use App\Domain\Task\TaskStatusEnum;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class CreateTaskCommand
{
    /**
     * @param TaskRepository $repository
     */
    public function __construct(protected TaskRepository $repository)
    {
    }

    /**
     * @param UserInterface $user
     * @param array $payload
     *
     * @return Task
     * @throws \DateMalformedStringException
     */
    public function __invoke(UserInterface $user, array $payload): Task
    {
        $task = new Task();
        $task->setTitle($payload['title']);
        $task->setDescription($payload['description'] ?? null);
        $task->setStatus(TaskStatusEnum::from($payload['status']));
        $task->setDueDate(new \DateTime($payload['dueDate']));
        $task->setUser($user);
        $this->repository->save($task, true);

        return $task;
    }
}
<?php

namespace App\Command\Task;

use App\Domain\Task\TaskStatusEnum;
use App\Entity\Task;
use App\Repository\TaskRepository;

class UpdateTaskCommand
{
    /**
     * @param TaskRepository $repository
     */
    public function __construct(protected TaskRepository $repository)
    {
    }

    /**
     * @param Task $task
     * @param array $payload
     *
     * @return Task
     * @throws \DateMalformedStringException
     */
    public function __invoke(Task $task, array $payload): Task
    {
        if (!empty($payload['title'])) {
            $task->setTitle($payload['title']);
        }
        if (!empty($payload['description'])) {
            $task->setDescription($payload['description']);
        }
        if (!empty($payload['status'])) {
            $task->setStatus(TaskStatusEnum::from($payload['status']));
        }
        if (!empty($payload['dueDate'])) {
            $task->setDueDate(new \DateTime($payload['dueDate']));
        }
        $this->repository->save($task, true);

        return $task;
    }
}
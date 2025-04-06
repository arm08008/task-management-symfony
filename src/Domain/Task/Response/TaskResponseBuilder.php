<?php

namespace App\Domain\Task\Response;

use App\Entity\Task;

class TaskResponseBuilder
{
    /**
     * Builds task response via Task entity
     *
     * @param Task $task
     * @return array
     */
    public function build(Task $task): array
    {
        return [
            "id" => $task->getId(),
            "userId" => $task->getUser()->getId(),
            "title" => $task->getTitle(),
            "description" => $task->getDescription(),
            "status" => $task->getStatus()->value,
            'dueDate' => $task->getDueDate()->format("Y-m-d"),
            'createdAt' => $task->getCreatedAt()->format("Y-m-d"),
        ];
    }

    /**
     * Builds tasks response via array of Tasks
     *
     * @param array $tasks
     * @return array
     */
    public function buildAsArray(array $tasks): array
    {
        $response = [];
        foreach ($tasks as $task) {
            $response[] = $this->build($task);
        }

        return $response;
    }
}
<?php

namespace App\Controller\Task;

use App\Command\Task\CreateTaskCommand;
use App\Command\Task\UpdateTaskCommand;
use App\Controller\ApiController;
use App\Domain\Task\Response\TaskResponseBuilder;
use App\Domain\Task\Validation\TaskCreateValidator;
use App\Domain\Task\Validation\TaskUpdateValidator;
use App\Repository\TaskRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends ApiController
{
    #[Route('/api/task/create', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        Security $security,
        TaskCreateValidator $taskCreateValidator,
        CreateTaskCommand $command,
        TaskResponseBuilder $responseBuilder
    ): Response
    {
        $payload = $this->getRequestPayload($request);
        $violations = $taskCreateValidator->validate($payload);
        if ($violations->count() > 0) {
            return $this->checkViolations($violations);
        }

        try {
            $task = ($command)($security->getUser(), $payload);
        } catch (\Exception $e) {
            return $this->errorResponse(
                errors: [
                    'msg' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }

        return $this->successResponse(
            message: 'Task created successfully',
            data: [
                'task' => $responseBuilder->build($task),
            ],
            statusCode: Response::HTTP_CREATED,
        );
    }

    #[Route('/api/task/update/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(
        int $id,
        Request $request,
        Security $security,
        TaskUpdateValidator $taskUpdateValidator,
        UpdateTaskCommand $command,
        TaskRepository $repository,
        TaskResponseBuilder $responseBuilder
    ): Response
    {
        $payload = $this->getRequestPayload($request);
        if (!count($payload)) {
            return $this->errorResponse(
                message: 'One of the parameters should be provided',
                statusCode: Response::HTTP_BAD_REQUEST
            );
        }

        $violations = $taskUpdateValidator->validate($payload);
        if ($violations->count() > 0) {
            return $this->checkViolations($violations);
        }

        $task = $repository->findOneBy([
                    'id' => $id,
                    'user' => $security->getUser()
                ]);
        if (!$task) {
            return $this->errorResponse(
                message: 'Entity not found with id ' . $id,
                statusCode: Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $task = ($command)($task, $payload);
        } catch (\Exception $e) {
            return $this->errorResponse(
                errors: [
                    'msg' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }

        return $this->successResponse(
            message: 'Task updated successfully',
            data: [
                'task' => $responseBuilder->build($task),
            ]
        );
    }

    #[Route('/api/task/list', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        Security $security,
        TaskRepository $repository,
        TaskResponseBuilder $responseBuilder
    ): Response
    {
        $queryParams = $request->query->all();
        $page = $queryParams['page'] ?? 1;
        $limit = $queryParams['limit'] ?? 10;
        $tasks = $repository->findUserTasksWithFilters(
            $security->getUser(),
            $queryParams,
            $page,
            $limit
        );

        return $this->successResponse(
            data: [
                'tasks' => $responseBuilder->buildAsArray($tasks),
                'pagination' => [
                    'currentPage' => $page,
                    'perPage' => $limit,
                    'totalItems' => count($tasks),
                    'totalPages' => ceil(count($tasks) / $limit)
                ]
            ]
        );
    }

    #[Route('/api/task/delete/{id}', name: 'task_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        Security $security,
        TaskRepository $taskRepository
    ): Response {
        $task = $taskRepository->findOneBy([
            'id' => $id,
            'user' => $security->getUser()
        ]);

        if (!$task) {
            return $this->errorResponse(
                message: 'Entity not found with id ' . $id,
                statusCode: Response::HTTP_NOT_FOUND,
            );
        }
        $taskRepository->remove($task, true);

        return $this->successResponse(
            message: 'Task deleted successfully',
        );
    }
}

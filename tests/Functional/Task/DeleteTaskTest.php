<?php

namespace App\Tests\Functional\Task;

use App\Domain\Task\TaskStatusEnum;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteTaskTest extends WebTestCase
{
    private KernelBrowser $client;
    private TaskRepository $taskRepository;
    private User $testUser;
    private User $otherUser;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->taskRepository = static::getContainer()->get(TaskRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        $this->taskRepository->createQueryBuilder('t')->delete()->getQuery()->execute();
        $userRepository->createQueryBuilder('u')->delete()->getQuery()->execute();

        $this->testUser = new User();
        $this->testUser->setEmail('test@example.com');
        $this->testUser->setPassword('password');
        $userRepository->save($this->testUser, true);

        $this->otherUser = new User();
        $this->otherUser->setEmail('other@example.com');
        $this->otherUser->setPassword('password');
        $userRepository->save($this->otherUser, true);

        $task1 = new Task();
        $task1->setTitle('Task to delete');
        $task1->setUser($this->testUser);
        $task1->setStatus(TaskStatusEnum::DONE);
        $this->taskRepository->save($task1, true);

        $task2 = new Task();
        $task2->setTitle('Other user task');
        $task2->setUser($this->otherUser);
        $task2->setStatus(TaskStatusEnum::TODO);
        $this->taskRepository->save($task2, true);
    }

    public function testDeleteTaskSuccessfully(): void
    {
        $this->client->loginUser($this->testUser);

        $task = $this->taskRepository->findOneBy(['user' => $this->testUser]);
        $taskId = $task->getId();

        $this->client->request('DELETE', "/api/task/delete/{$taskId}");

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Task deleted successfully', $responseData['message']);

        $deletedTask = $this->taskRepository->find($taskId);
        $this->assertNull($deletedTask);
    }

    public function testDeleteNonExistentTask(): void
    {
        $this->client->loginUser($this->testUser);

        $nonExistentId = 9999;
        $this->client->request('DELETE', "/api/task/delete/{$nonExistentId}");

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals("Entity not found with id {$nonExistentId}", $responseData['message']);
    }
}
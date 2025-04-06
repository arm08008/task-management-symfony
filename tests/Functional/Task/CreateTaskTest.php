<?php

namespace App\Tests\Functional\Task;

use App\Domain\Task\TaskStatusEnum;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateTaskTest extends WebTestCase
{
    private Generator $faker;
    private KernelBrowser $client;
    private TaskRepository $taskRepository;
    private User $testUser;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->client = $this->createClient();
        $this->taskRepository = $this->getContainer()->get(TaskRepository::class);
        $userRepository = $this->getContainer()->get(UserRepository::class);

        $this->taskRepository->createQueryBuilder('t')->delete()->getQuery()->execute();
        $userRepository->createQueryBuilder('u')->delete()->getQuery()->execute();

        $this->testUser = new User();
        $this->testUser->setEmail($this->faker->email());
        $this->testUser->setPassword($this->faker->password());
        $userRepository->save($this->testUser, true);
    }

    public function testCreateTaskSuccessfully(): void
    {
        $this->client->loginUser($this->testUser);

        $payload = [
            'title' => $this->faker->title(),
            'description' => $this->faker->sentence(),
            'status' => TaskStatusEnum::DONE->value,
            'dueDate' => $this->faker->date(),
        ];

        $this->client->request(
            'POST',
            '/api/task/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals('Task created successfully', $responseData['message']);
        $this->assertArrayHasKey('task', $responseData['data']);

        $taskData = $responseData['data']['task'];
        $this->assertEquals($payload['title'], $taskData['title']);
        $this->assertEquals($payload['description'], $taskData['description']);
        $this->assertEquals($payload['status'], $taskData['status']);
        $this->assertEquals($payload['dueDate'], $taskData['dueDate']);
        $this->assertEquals($this->testUser->getId(), $taskData['userId']);

        $task = $this->taskRepository->find($taskData['id']);
        $this->assertNotNull($task);
    }

    public function testCreateTaskUnauthenticated(): void
    {
        $payload = [
            'title' => $this->faker->title(),
            'status' => TaskStatusEnum::DONE->value,
            'dueDate' => $this->faker->date(),
        ];

        $this->client->request(
            'POST',
            '/api/task/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }
}
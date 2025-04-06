<?php

namespace App\Tests\Unit\Command\Task;

use App\Command\Task\CreateTaskCommand;
use App\Domain\Task\TaskStatusEnum;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class CreateTaskCommandTest extends TestCase
{
    private Generator $faker;
    private TaskRepository $repository;
    private CreateTaskCommand $command;
    private UserInterface $user;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->repository = $this->createMock(TaskRepository::class);
        $this->command = new CreateTaskCommand($this->repository);
        $this->user = $this->createMock(User::class);
    }

    public function testCommand()
    {
        $payload = [
            'title' => $this->faker->title(),
            'description' => $this->faker->sentence(),
            'status' => TaskStatusEnum::TODO->value,
            'dueDate' => $this->faker->date(),
        ];
        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Task::class), true);

        $task = ($this->command)($this->user, $payload);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($payload['title'], $task->getTitle());
        $this->assertEquals($payload['description'], $task->getDescription());
        $this->assertEquals(TaskStatusEnum::TODO, $task->getStatus());
        $this->assertEquals($payload['dueDate'], $task->getDueDate()->format('Y-m-d'));
        $this->assertSame($this->user, $task->getUser());
    }
}
<?php

namespace App\Tests\Unit\Command\Task;

use App\Command\Task\UpdateTaskCommand;
use App\Domain\Task\TaskStatusEnum;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

class UpdateTaskCommandTest extends TestCase
{
    private TaskRepository $repository;
    private UpdateTaskCommand $command;
    private Generator $faker;
    private Task $existingTask;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->repository = $this->createMock(TaskRepository::class);
        $this->command = new UpdateTaskCommand($this->repository);

        $this->existingTask = new Task();
        $this->existingTask->setTitle('Original Title');
        $this->existingTask->setDescription('Original Description');
        $this->existingTask->setStatus(TaskStatusEnum::TODO);
        $this->existingTask->setDueDate(new \DateTime('2023-01-01'));
    }

    public function testUpdatesAllFieldsWhenProvided()
    {
        $payload = [
            'title' => $this->faker->title(),
            'description' => $this->faker->sentence(),
            'status' => TaskStatusEnum::DONE->value,
            'dueDate' => $this->faker->date(),
        ];

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->existingTask, true);

        $updatedTask = ($this->command)($this->existingTask, $payload);

        $this->assertSame($this->existingTask, $updatedTask);
        $this->assertEquals($payload['title'], $updatedTask->getTitle());
        $this->assertEquals($payload['description'], $updatedTask->getDescription());
        $this->assertEquals(TaskStatusEnum::DONE, $updatedTask->getStatus());
        $this->assertEquals($payload['dueDate'], $updatedTask->getDueDate()->format('Y-m-d'));
    }
}
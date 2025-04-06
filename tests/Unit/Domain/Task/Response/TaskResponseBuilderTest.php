<?php

namespace App\Tests\Unit\Domain\Task\Response;

use App\Domain\Task\Response\TaskResponseBuilder;
use App\Domain\Task\TaskStatusEnum;
use App\Entity\Task;
use App\Entity\User;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

class TaskResponseBuilderTest extends TestCase
{
    private TaskResponseBuilder $builder;
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->builder = new TaskResponseBuilder();
    }

    public function testBuildSingleTaskResponse()
    {
        $task = new Task();
        $task->setUser(new User());
        $task->setTitle($this->faker->title());
        $task->setDescription($this->faker->sentence());
        $task->setStatus(TaskStatusEnum::IN_PROGRESS);
        $task->setDueDate($this->faker->dateTime());
        $task->setCreatedAt(new \DateTimeImmutable($this->faker->date()));

        $response = $this->builder->build($task);

        $this->assertIsArray($response);
        $this->assertEquals($task->getId(), $response['id']);
        $this->assertEquals($task->getUser()->getId(), $response['userId']);
        $this->assertEquals($task->getTitle(), $response['title']);
        $this->assertEquals($task->getDescription(), $response['description']);
        $this->assertEquals($task->getStatus()->value, $response['status']);
        $this->assertEquals($task->getDueDate()->format('Y-m-d'), $response['dueDate']);
        $this->assertEquals($task->getCreatedAt()->format('Y-m-d'), $response['createdAt']);
    }

    public function testBuildAsArrayWithMultipleTasks()
    {
        $tasks = [];
        $user = new User();
        $title = $this->faker->title();
        for ($i = 0; $i < 3; $i++) {
            $task = new Task();
            $task->setUser($user);
            $task->setTitle($title);
            $task->setStatus(TaskStatusEnum::DONE);
            $task->setDueDate($this->faker->dateTime());
            $task->setCreatedAt(new \DateTimeImmutable($this->faker->date()));
            $tasks[] = $task;
        }

        $response = $this->builder->buildAsArray($tasks);

        $this->assertCount(3, $response);
        $this->assertEquals($title, $response[0]['title']);
        $this->assertEquals($title, $response[1]['title']);
        $this->assertEquals($title, $response[2]['title']);
    }
}
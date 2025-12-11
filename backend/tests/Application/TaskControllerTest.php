<?php

namespace App\Tests\Feature;

use App\Entity\Project;
use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);

        $conn = $this->em->getConnection();
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $conn->executeStatement('TRUNCATE TABLE task');
        $conn->executeStatement('TRUNCATE TABLE project');
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function testGetTasksForProjectReturnsList(): void
    {
        // Arrange: create project
        $project = new Project();
        $project->setName('API Project');
        $project->setDescription('Test project');
        $this->em->persist($project);
        $this->em->flush();

        // Arrange: create two tasks for this project
        $task1 = new Task();
        $task1->setProject($project);
        $task1->setTitle('First task');
        $task1->setDescription('First');
        $task1->setStatus(TaskStatus::TODO);
        $task1->setPriority(TaskPriority::HIGH);
        $this->em->persist($task1);

        $task2 = new Task();
        $task2->setProject($project);
        $task2->setTitle('Second task');
        $task2->setDescription('Second');
        $task2->setStatus(TaskStatus::IN_PROGRESS);
        $task2->setPriority(TaskPriority::MEDIUM);
        $this->em->persist($task2);

        $this->em->flush();

        // Act
        $this->client->request('GET', '/api/projects/'.$project->getId().'/tasks');

        // Assert
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertSame('First task', $data[0]['title']);
    }

    public function testGetTasksForProjectWithInvalidStatusReturns400(): void
    {
        $project = new Project();
        $project->setName('API Project');
        $this->em->persist($project);
        $this->em->flush();

        $this->client->request(
            'GET',
            '/api/projects/'.$project->getId().'/tasks?status=invalid'
        );

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testCreateTaskSuccess(): void
    {
        // Arrange
        $project = new Project();
        $project->setName('API Project');
        $this->em->persist($project);
        $this->em->flush();

        // Act
        $this->client->request(
            'POST',
            '/api/projects/'.$project->getId().'/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'New task',
                'description' => 'Testing create',
                'status' => 'todo',
                'priority' => 'high',
            ])
        );

        // Assert
        $this->assertSame(201, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('New task', $data['title']);
        $this->assertSame('todo', $data['status']);
        $this->assertSame('high', $data['priority']);
    }

    public function testCreateTaskDefaultsStatusToTodoWhenMissing(): void
    {
        // Arrange
        $project = new Project();
        $project->setName('API Project');
        $this->em->persist($project);
        $this->em->flush();

        // Act: no status field
        $this->client->request(
            'POST',
            '/api/projects/'.$project->getId().'/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'No status task',
                'description' => 'Should default to todo',
                'priority' => 'medium',
            ])
        );

        // Assert
        $this->assertSame(201, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('No status task', $data['title']);
        $this->assertSame('todo', $data['status']); // default should be todo
        $this->assertSame('medium', $data['priority']);
    }

    public function testCreateTaskValidationErrorWhenTitleOrPriorityMissing(): void
    {
        // Arrange
        $project = new Project();
        $project->setName('API Project');
        $this->em->persist($project);
        $this->em->flush();

        // Act: missing title and priority
        $this->client->request(
            'POST',
            '/api/projects/'.$project->getId().'/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'description' => 'Missing required fields',
            ])
        );

        // Assert
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testCreateTaskInvalidStatusReturns400(): void
    {
        // Arrange: project
        $project = new Project();
        $project->setName('API Project');
        $this->em->persist($project);
        $this->em->flush();

        // Act: invalid status
        $this->client->request(
            'POST',
            '/api/projects/'.$project->getId().'/tasks?status=invalid',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Bad status task',
                'description' => 'Invalid status',
                'status' => 'invalid',
                'priority' => 'medium',
            ])
        );

        // Assert
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdateTaskValidStatusTransition(): void
    {
        // Arrange: project & task in TODO
        $project = new Project();
        $project->setName('API Project');
        $this->em->persist($project);

        $task = new Task();
        $task->setProject($project);
        $task->setTitle('Move to in_progress');
        $task->setDescription('Test transition');
        $task->setStatus(TaskStatus::TODO);
        $task->setPriority(TaskPriority::MEDIUM);
        $this->em->persist($task);

        $this->em->flush();

        // Act: todo -> in_progress
        $this->client->request(
            'PATCH',
            '/api/tasks/'.$task->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'status' => 'in_progress',
            ])
        );

        // Assert
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('in_progress', $data['status']);

        // Reload from DB to be sure
        $this->em->clear();
        $reloaded = $this->em->getRepository(Task::class)->find($task->getId());
        $this->assertSame(TaskStatus::IN_PROGRESS, $reloaded->getStatus());
    }

    public function testUpdateTaskInvalidStatusTransitionReturns400(): void
    {
        // Arrange: project & task in DONE
        $project = new Project();
        $project->setName('API Project');
        $this->em->persist($project);

        $task = new Task();
        $task->setProject($project);
        $task->setTitle('Already done');
        $task->setDescription('Cannot move back');
        $task->setStatus(TaskStatus::DONE);
        $task->setPriority(TaskPriority::HIGH);
        $this->em->persist($task);

        $this->em->flush();

        // Act: done -> todo (invalid)
        $this->client->request(
            'PATCH',
            '/api/tasks/'.$task->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'status' => 'todo',
            ])
        );

        // Assert
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);

        // Status should remain DONE in DB
        $this->em->clear();
        $reloaded = $this->em->getRepository(Task::class)->find($task->getId());
        $this->assertSame(TaskStatus::DONE, $reloaded->getStatus());
    }

    public function testDeleteTaskRemovesIt(): void
    {
        $project = new Project();
        $project->setName('API Project');
        $this->em->persist($project);

        $task = new Task();
        $task->setProject($project);
        $task->setTitle('Delete me');
        $task->setDescription('To be deleted');
        $task->setStatus(TaskStatus::TODO);
        $task->setPriority(TaskPriority::LOW);
        $this->em->persist($task);
        $this->em->flush();

        $taskId = $task->getId();
        $this->client->request('DELETE', "/api/tasks/$taskId");

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        $reloaded = $this->em->getRepository(Task::class)->find($taskId);
        $this->assertNull($reloaded);
    }

    public function testDeleteNonExistingTaskReturns404(): void
    {
        $this->client->request('DELETE', '/api/tasks/999999');

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}

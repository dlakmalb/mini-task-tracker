<?php

namespace App\Tests\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $em = null;
    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $connection = $this->em->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('TRUNCATE TABLE task');
        $connection->executeStatement('TRUNCATE TABLE project');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function testGetProjectsReturnsList(): void
    {
        // Arrange: create one project
        $project = new Project();
        $project->setName('Test Project');
        $project->setDescription('Sample');
        $this->em->persist($project);
        $this->em->flush();

        $this->client->request('GET', '/api/projects');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertSame('Test Project', $data[0]['name']);
    }

    public function testCreateProjectSuccess(): void
    {
        $this->client->request(
            'POST',
            '/api/projects',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'New Project',
                'description' => 'Created from test',
            ])
        );

        $this->assertSame(201, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('New Project', $data['name']);
    }

    public function testCreateProjectValidationErrorWhenNameMissing(): void
    {
        $this->client->request(
            'POST',
            '/api/projects',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'description' => 'No name here',
            ])
        );

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }
}

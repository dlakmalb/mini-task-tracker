<?php

namespace App\Service;

use App\Entity\Project;
use App\Repository\ProjectRepository;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    /**
     * @return Project[]
     */
    public function getAllProjects(): array
    {
        return $this->projectRepository->findBy([], ['createdAt' => 'DESC']);
    }

    public function createProject(string $name, ?string $description): Project
    {
        $project = new Project();

        $project->setName($name);
        $project->setDescription($description);

        $this->projectRepository->save($project, true);

        return $project;
    }
}

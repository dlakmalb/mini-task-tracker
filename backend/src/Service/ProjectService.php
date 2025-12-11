<?php

namespace App\Service;

use App\DTO\CreateProjectRequestDTO;
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

    public function createProject(CreateProjectRequestDTO $dto): Project
    {
        $project = new Project();

        $project->setName($dto->name);
        $project->setDescription($dto->description);

        $this->projectRepository->save($project, true);

        return $project;
    }
}

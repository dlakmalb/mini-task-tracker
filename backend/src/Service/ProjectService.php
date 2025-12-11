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
     * @return array{0: Project[], 1: int} [projects, total]
     */
    public function getPaginatedProjects(int $page, int $limit): array
    {
        return $this->projectRepository->findPaginatedProjects($page, $limit);
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

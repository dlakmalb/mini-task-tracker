<?php

namespace App\Controller;

use App\DTO\CreateProjectRequestDTO;
use App\Entity\Project;
use App\Service\ProjectService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectController
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {
    }

    /**
     * Get all projects
     *
     * @return JsonResponse
     */
    #[Route('/api/projects', name: 'api_projects_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            $projects = $this->projectService->getAllProjects();

            $data = array_map(
                fn (Project $project) => $this->organiseProjectData($project),
                $projects
            );

            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Throwable $e) {
            return new JsonResponse(
                ['error' => 'Failed to fetch projects.'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Create a project
     */
    #[Route('/api/projects', name: 'api_projects_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        ProjectService $projectService,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = new CreateProjectRequestDTO();

        $dto->name = $data['name'] ?? null;
        $dto->description = $data['description'] ?? null;

        // Validate input
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return new JsonResponse(
                ['error' => (string) $errors],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Create project
        $project = $projectService->createProject($dto);

        return new JsonResponse(
            $this->organiseProjectData($project),
            JsonResponse::HTTP_CREATED
        );
    }

    private function organiseProjectData(Project $project): array
    {
        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'created_at' => $project->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}

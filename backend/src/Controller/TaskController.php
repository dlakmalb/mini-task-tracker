<?php

namespace App\Controller;

use App\DTO\CreateTaskRequestDTO;
use App\DTO\UpdateTaskRequestDTO;
use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Helper\PaginationHelper;
use App\Repository\ProjectRepository;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TaskController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly TaskService $taskService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Get tasks related to a project for given params.
     */
    #[Route('/api/projects/{projectId}/tasks', name: 'api_project_tasks_index', methods: ['GET'])]
    public function index(int $projectId, Request $request): JsonResponse
    {
        $project = $this->projectRepository->find($projectId);

        if (!$project) {
            return new JsonResponse(
                ['error' => 'Project not found.'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        // Parse query parameters
        $statusParam = $request->query->get('status');
        $titleParam = $request->query->get('title');

        $status = null;
        $title = $titleParam ? trim($titleParam) : null;

        if (null !== $statusParam && '' !== $statusParam) {
            try {
                $status = TaskStatus::from($statusParam);
            } catch (\ValueError) {
                return new JsonResponse(
                    ['error' => 'Invalid status value. Allowed: todo, in_progress, done.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
        }

        [$page, $limit] = PaginationHelper::fromRequest($request);

        // Get tasks from service
        [$tasks, $total] = $this->taskService->getTasksForProject(
            project: $project,
            status: $status,
            title: $title,
            page: $page,
            limit: $limit,
        );

        // Format response data
        $data = array_map(
            fn (Task $task) => $this->organiseTaskData($task),
            $tasks
        );

        return new JsonResponse([
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => (int) ceil($total / $limit),
            ],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Create a task for a project.
     */
    #[Route('/api/projects/{projectId}/tasks', name: 'api_project_tasks_create', methods: ['POST'])]
    public function create(
        int $projectId,
        Request $request,
        ValidatorInterface $validator,
    ): JsonResponse {
        $project = $this->projectRepository->find($projectId);

        if (!$project) {
            return new JsonResponse(
                ['error' => 'Project not found.'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new CreateTaskRequestDTO();
        $dto->title = $data['title'] ?? null;
        $dto->description = $data['description'] ?? null;
        $dto->status = $data['status'] ?? null;
        $dto->priority = $data['priority'] ?? null;

        // Validate DTO
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return new JsonResponse(
                ['error' => (string) $errors],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // enum validation (status, priority values)
        try {
            // will default status inside service if null
            if (null !== $dto->status) {
                TaskStatus::from($dto->status);
            }

            TaskPriority::from($dto->priority);
        } catch (\ValueError) {
            return new JsonResponse(
                ['error' => 'Invalid status or priority value.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $task = $this->taskService->createForProject($project, $dto);

        return new JsonResponse($this->organiseTaskData($task), JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/tasks/{id}', name: 'api_tasks_update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $task = $this->em->getRepository(Task::class)->find($id);

        if (!$task) {
            return new JsonResponse(
                ['error' => 'Task not found.'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new UpdateTaskRequestDTO();
        $dto->status = $data['status'] ?? null;
        $dto->priority = $data['priority'] ?? null;

        if (null === $dto->status && null === $dto->priority) {
            return new JsonResponse(
                ['error' => 'At least one of status or priority must be provided.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // enum validation (status, priority values)
        try {
            if (null !== $dto->status) {
                TaskStatus::from($dto->status);
            }
            if (null !== $dto->priority) {
                TaskPriority::from($dto->priority);
            }
        } catch (\ValueError) {
            return new JsonResponse(
                ['error' => 'Invalid status or priority value.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $task = $this->taskService->updateTask($task, $dto);
        } catch (\LogicException $e) {
            return new JsonResponse(
                ['error' => 'Invalid status transition.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse($this->organiseTaskData($task), JsonResponse::HTTP_OK);
    }

    #[Route('/api/tasks/{id}', name: 'api_tasks_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $task = $this->em->getRepository(Task::class)->find($id);

        if (!$task) {
            return new JsonResponse(
                ['error' => 'Task not found.'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $this->taskService->deleteTask($task);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function organiseTaskData(Task $task): array
    {
        return [
            'id' => $task->getId(),
            'projectId' => $task->getProject()->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus()->value,
            'priority' => $task->getPriority()->value,
            'createdAt' => $task->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}

<?php

namespace App\Service;

use App\DTO\CreateTaskRequestDTO;
use App\DTO\UpdateTaskRequestDTO;
use App\Entity\Project;
use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TaskService
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $em,
        private readonly TaskStatusTransitionService $transitionService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return array{0: Task[], 1: int} [tasks, total]
     */
    public function getTasksForProject(
        Project $project,
        ?TaskStatus $status,
        ?string $title,
        int $page,
        int $limit,
    ): array {
        return $this->taskRepository->findByProjectAndFilters(
            project: $project,
            status: $status,
            title: $title,
            page: $page,
            limit: $limit,
        );
    }

    public function createForProject(Project $project, CreateTaskRequestDTO $dto): Task
    {
        // Create new Task
        $task = new Task();
        $task->setProject($project);
        $task->setTitle(trim((string) $dto->title));
        $task->setDescription($dto->description ? trim($dto->description) : null);

        $statusString = $dto->status ?: TaskStatus::TODO->value;
        $status = TaskStatus::from($statusString);
        $task->setStatus($status);

        $priority = TaskPriority::from($dto->priority);
        $task->setPriority($priority);

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

    public function updateTask(Task $task, UpdateTaskRequestDTO $dto): Task
    {
        $task->setTitle(trim((string) $dto->title));
        $task->setDescription($dto->description ? trim($dto->description) : null);

        // Status update
        if (null !== $dto->status) {
            $current = $task->getStatus();
            $new = TaskStatus::from($dto->status);

            if (!$this->transitionService->canTransition($current, $new)) {
                throw new \LogicException('Invalid status transition.');
            }

            if ($this->transitionService->isFastTracked($current, $new)) {
                // Here we could log as "fasttracked"
                $this->logger->info('Task was fast-tracked from todo to done', [
                    'task_id' => $task->getId(),
                    'project_id' => $task->getProject()->getId(),
                ]);
            }

            $task->setStatus($new);
        }

        // Priority update
        if (null !== $dto->priority) {
            $priority = TaskPriority::from($dto->priority);
            $task->setPriority($priority);
        }

        $this->em->flush();

        return $task;
    }

    public function deleteTask(Task $task): void
    {
        $this->em->remove($task);
        $this->em->flush();
    }
}

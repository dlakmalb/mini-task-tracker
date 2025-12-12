<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Task;
use App\Enum\TaskStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return array{0: Task[], 1: int} [tasks, total]
     */
    public function findByProjectAndFilters(
        Project $project,
        ?TaskStatus $status,
        ?string $title,
        int $page,
        int $limit,
    ): array {
        $queryBuilder = $this->createQueryBuilder('task')
            ->andWhere('task.project = :project')
            ->setParameter('project', $project)
            ->orderBy('task.createdAt', 'DESC');

        if (null !== $status) {
            $queryBuilder->andWhere('task.status = :status')
               ->setParameter('status', $status->value);
        }

        if (null !== $title && '' !== $title) {
            $queryBuilder->andWhere('LOWER(task.title) LIKE :title')
               ->setParameter('title', '%'.mb_strtolower($title).'%');
        }

        $countQueryBuilder = clone $queryBuilder;

        $total = (int) $countQueryBuilder
            ->select('COUNT(task.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $tasks = $queryBuilder->getQuery()->getResult();

        return [$tasks, $total];
    }
}

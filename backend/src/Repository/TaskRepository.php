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
     * @return Task[]
     */
    public function findByProjectAndFilters(
        Project $project,
        ?TaskStatus $status,
        ?string $title,
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

        return $queryBuilder->getQuery()->getResult();
    }
}

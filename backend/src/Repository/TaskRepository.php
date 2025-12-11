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
        ?string $search,
    ): array {
        $qb = $this->createQueryBuilder('task')
            ->andWhere('task.project = :project')
            ->setParameter('project', $project)
            ->orderBy('task.createdAt', 'DESC');

        if (null !== $status) {
            $qb->andWhere('task.status = :status')
               ->setParameter('status', $status->value);
        }

        if (null !== $search && '' !== $search) {
            $qb->andWhere('LOWER(task.title) LIKE :q')
               ->setParameter('q', '%'.mb_strtolower($search).'%');
        }

        return $qb->getQuery()->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @return array{0: Project[], 1: int} [projects, total]
     */
    public function findPaginatedProjects(int $page, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('project')
            ->orderBy('project.createdAt', 'DESC');

        // clone for count
        $countQueryBuilder = clone $queryBuilder;

        $total = (int) $countQueryBuilder
            ->select('COUNT(project.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $projects = $queryBuilder->getQuery()->getResult();

        return [$projects, $total];
    }

    public function save(Project $project, bool $flush = false): void
    {
        $em = $this->getEntityManager();

        $em->persist($project);

        if ($flush) {
            $em->flush();
        }
    }
}

<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task, bool $flush = false): void
    {
        $this->getEntityManager()->persist($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $task, bool $flush = false): void
    {
        $this->getEntityManager()->remove($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findUserTasksWithFilters(
        UserInterface $user,
        array $params = [],
        int $page = 1,
        int $limit = 10
    ): array {
        $queryBuilder = $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user);

        if (!empty($params['status'])) {
            $queryBuilder
                ->andWhere('t.status = :status')
                ->setParameter('status', $params['status']);
        }

        if (!empty($params['dueDate'])) {
            $queryBuilder
                ->andWhere('t.dueDate = :dueDate')
                ->setParameter('dueDate', $params['dueDate']);
        }

        if (!empty($params['title'])) {
            $title = $params['title'];
            $queryBuilder
                ->andWhere('LOWER(t.title) LIKE LOWER(:search)')
                ->setParameter('search', '%'.$title.'%');
        }

        return $queryBuilder
            ->orderBy('t.dueDate', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 *
 * @method null|Employee find($id, $lockMode = null, $lockVersion = null)
 * @method null|Employee findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
    protected const STATUS_TYPES = [
        1 => 'работает',
        2 => 'декрет',
        3 => 'уволен',
    ];

    protected const REASON_TYPES = [
        1 => 'не пройден испытательный срок',
        2 => 'проблемы с дисциплиной',
        3 => 'не справлялся с поставленными задачами',
        4 => 'сокращение',
        5 => 'предложение о работе с высокой заработной платой',
        6 => 'потерял ценность',
        7 => 'не видит для себя профессионального развития',
        8 => 'хочет сменить должность/направление',
        9 => 'выгорание',
        10 => 'релокация',
    ];

    protected const CATEGORY_TYPE = [
        1 => 'добровольная',
        2 => 'принудительная',
        3 => 'нежелательная',
    ];

    protected const COLORS = [
        '#2191FB',
        '#8CDEDC',
        '#329F5B',
        '#8380B6',
        '#6465A5',
        '#177E89',
        '#F40076',
        '#FF6700',
        '#A37C27',
        '#F3CD05',
        '#282726',
        '#2D1115',
        '#CEFF1A',
        '#A7414A',
        '#D72638',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function save(Employee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Employee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array
     */
    public function getDepartmentsNames(): array
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->distinct()
            ->select('e.department');

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @return float
     *               Получение опыта работы
     */
    protected function getWorkExperience(\DateTimeImmutable $dateStart, ?\DateTimeImmutable $dateEnd = null): float
    {
        if (null === $dateEnd) {
            $dateEnd = new \DateTimeImmutable();
        }
        $interval = $dateStart->diff($dateEnd);

        return round(fdiv($interval->days, 365.0), 2);
    }

    /**
     * @return int
     *             Общее количество уволенных
     */
    protected function getTotalDismissedEmployees(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        string|null $department
    ): int {
        $query = $this->createQueryBuilder('e');
        $query
            ->select('e')
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo);

        if (!is_null($department)) {
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        /**
         * @var array $result
         */
        $result = $query->getQuery()->getArrayResult();

        // всего уволено
        return count($result);
    }
}

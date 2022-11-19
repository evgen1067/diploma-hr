<?php

namespace App\Repository;

use App\Entity\Employee;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 *
 * @method Employee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
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

    private const STATUS_TYPES = [
        1 => 'работает',
        2 => 'декрет',
        3 => 'уволен',
    ];

    private const REASON_TYPES = [
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

    private const CATEGORY_TYPE = [
        1 => 'добровольная',
        2 => 'принудительная',
        3 => 'нежелательная',
    ];

    public function remove(Employee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    private function buildQuery(QueryBuilder $query, array $filter): QueryBuilder
    {
        foreach ($filter as $key => $item) {

            # текст содержит
            if (isset($item['type']) && $item['type'] === 'text_contains') {
                $query->andWhere($query->expr()->like('LOWER(e.' . $key . ')', ':param' . $key))
                    ->setParameter('param' . $key, '%' . mb_strtolower($item['value']) . '%');
            }

            # текст не содержит
            if (isset($item['type']) && $item['type'] === 'text_not_contains') {
                $query->andWhere($query->expr()->notLike('LOWER(e.' . $key . ')', ':param' . $key))
                    ->setParameter('param' . $key, '%' . mb_strtolower($item['value']) . '%');
            }

            # начинается с
            if (isset($item['type']) && $item['type'] === 'text_start') {
                $query->andWhere('e.' . $key . ' LIKE :param' . $key)
                    ->setParameter('param' . $key, $item['value'] . '%');
            }

            # заканчивается на
            if (isset($item['type']) && $item['type'] === 'text_end') {
                $query->andWhere('e.' . $key . ' LIKE :param' . $key)
                    ->setParameter('param' . $key,  '%' . $item['value']);
            }

            # Текст в точности || Число равно
            if (isset($item['type']) && ($item['type'] === 'text_accuracy' || $item['type'] === 'number_equal' || $item['type'] === 'list')) {
                $query->andWhere('e.' . $key . ' = :param' . $key)
                    ->setParameter('param' . $key, $item['value']);
            }

            # Число не равно
            if (isset($item['type']) && $item['type'] === 'number_not_equal') {
                $query->andWhere('e.' . $key . ' != :param' . $key)
                    ->setParameter('param' . $key, $item['value']);
            }
            # Неравенство
            if (isset($item['type']) && $item['type'] === 'number_inequality') {
                if (isset($item['valueFrom'], $item['valueTo'])) {
                    # Строгое неравенство
                    if (isset($item['isStrict']) && $item['isStrict'] === true) {
                        if ($item['valueFrom'] !== '' && $item['valueTo'] === '') {
                            $query->andWhere('e.' . $key . ' > :param' . $key)
                                ->setParameter('param' . $key, $item['valueFrom']);
                        } else if ($item['valueTo'] !== '' && $item['valueFrom'] === '') {
                            $query->andWhere('e.' . $key . ' < :param' . $key)
                                ->setParameter('param' . $key, $item['valueTo']);
                        } else if ($item['valueTo'] !== '' && $item['valueFrom'] !== '') {
                            $query->andWhere('e.' . $key . ' < :param2' . $key . ' AND ' . 'e.' . $key . ' > :param1' . $key)
                                ->setParameter('param1' . $key, $item['valueFrom'])
                                ->setParameter('param2' . $key, $item['valueTo']);
                        }
                    }
                    # Нестрогое неравенство
                    else if (isset($item['isStrict']) && $item['isStrict'] === false) {
                        if ($item['valueFrom'] !== '' && $item['valueTo'] === '') {
                            $query->andWhere('e.' . $key . ' >= :param' . $key)
                                ->setParameter('param' . $key, $item['valueFrom']);
                        } else if ($item['valueTo'] !== '' && $item['valueFrom'] === '') {
                            $query->andWhere('e.' . $key . ' <= :param' . $key)
                                ->setParameter('param' . $key, $item['valueTo']);
                        } else if ($item['valueTo'] !== '' && $item['valueFrom'] !== '') {
                            $query->andWhere('e.' . $key . ' BETWEEN :param1' . $key . ' AND :param2' . $key)
                                ->setParameter('param1' . $key, $item['valueFrom'])
                                ->setParameter('param2' . $key, $item['valueTo']);
                        }
                    }
                }
            }

            if (isset($item['type']) && $item['type'] === 'date_day') {
                $query->andWhere('e.' . $key . ' = :param' . $key)
                    ->setParameter('param' . $key, DateTimeImmutable::createFromFormat('d.m.Y', $item['value']));
            }

            if (isset($item['type']) && $item['type'] === 'date_before') {
                $query->andWhere('e.' . $key . ' < :param' . $key)
                    ->setParameter('param' . $key, DateTimeImmutable::createFromFormat('d.m.Y', $item['value']));
            }

            if (isset($item['type']) && $item['type'] === 'date_after') {
                $query->andWhere('e.' . $key . ' > :param' . $key)
                    ->setParameter('param' . $key, DateTimeImmutable::createFromFormat('d.m.Y', $item['value']));
            }
        }
        return $query;
    }

    private function getWorkExperience(DateTimeImmutable $dateStart, ?DateTimeImmutable $dateEnd = null): float
    {
        if ($dateEnd === null) {
            $dateEnd = new DateTimeImmutable();
        }
        $interval = $dateStart->diff($dateEnd);
        return round($interval->y + fdiv($interval->m, 12.0), 2);
    }

    public function getTableData(array|null $filter): array
    {
        $query = $this->createQueryBuilder('e');

        $query->select('e');

        if ($filter) {
            $query = $this->buildQuery($query, $filter);
        }

        $query->orderBy('e.dateOfEmployment');

        /**
         * @var array $table
         */
        $table = $query->getQuery()->getArrayResult();

        foreach ($table as $key => $item) {
            $table[$key]['workExperience'] = $this->getWorkExperience(
                $item['dateOfEmployment'],
                $item['dateOfDismissal'] ?: null
            );
            $table[$key]['dateOfEmployment'] = $table[$key]['dateOfEmployment']->format('d.m.Y');
            $table[$key]['status'] = self::STATUS_TYPES[$table[$key]['status']];
            if (isset($item['dateOfDismissal'])) {
                $table[$key]['dateOfDismissal'] = $table[$key]['dateOfDismissal']->format('d.m.Y');
                $table[$key]['reasonForDismissal'] = self::REASON_TYPES[$table[$key]['reasonForDismissal']];
                $table[$key]['categoryOfDismissal'] = self::CATEGORY_TYPE[$table[$key]['categoryOfDismissal']];
            }
        }
        return $table;
    }

    public function findEmployeeInArray(int $id): array
    {
        $query = $this->createQueryBuilder('e');

        $query
            ->select('e')
            ->andWhere('e.id = :id')
            ->setParameter('id', $id);
        $result = $query->getQuery()->getArrayResult();
       if (count($result) > 0) {
           $result = $result[0];
           $result['dateOfEmployment'] = $result['dateOfEmployment']->format('d.m.Y');
           if (isset($result['dateOfDismissal'])) {
               $result['dateOfDismissal'] = $result['dateOfDismissal']->format('d.m.Y');
           }
       }
        return $result;
    }
}

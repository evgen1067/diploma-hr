<?php

namespace App\Repository;

use App\Entity\Employee;
use Cassandra\Date;
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

    private const COLORS = [
        '#2191FB',
        '#8CDEDC',
        '#329F5B',
        '#8380B6',
        '#177E89',
        '#F40076',
        '#FF6700',
        '#2D1115',
        '#CEFF1A',
        '#D72638',
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
        return round(fdiv($interval->days, 365.0), 2);
    }

    public function getTableData(array|null $filter): array
    {
        $query = $this->createQueryBuilder('e');

        $query->select("e");

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

    public function findDataForLayoffsChart($department, $range, $work): array
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.status = 3');

        $labels = [];
        $datasets = [
            'label' => 'по всей компании',
            'backgroundColor' => self::COLORS,
            'borderWidth' => 1,
            'data' => [],
        ];

        if ($department) {
            $datasets['label'] = $department;
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        if ($range) {
            if ($range === 'Месяц') {
                $date = new DateTimeImmutable('-1 month');
                $query->andWhere('e.dateOfDismissal > :dateDis')
                    ->setParameter('dateDis', $date);
                $datasets['label'] .= ' за период с ' . $date->format('d.m.Y');
            }

            if ($range === 'Квартал') {
                $date = new DateTimeImmutable('-3 month');
                $query->andWhere('e.dateOfDismissal > :dateDis')
                    ->setParameter('dateDis', $date);
                $datasets['label'] .= ' за период с ' . $date->format('d.m.Y');
            }

            if ($range === 'Год') {
                $date = new DateTimeImmutable('-1 year');
                $query->andWhere('e.dateOfDismissal > :dateDis')
                    ->setParameter('dateDis', $date);
                $datasets['label'] .= ' за период с ' . $date->format('d.m.Y');
            }
        }

        $reasonData = [];
        $categoryData = [];

        /**
         * @var array $result
         */
        $result = $query->getQuery()->getArrayResult();

        if ($work) {
            $resultSet = [];
            foreach ($result as $key => $item) {
                $workExp = $this->getWorkExperience(
                    $item['dateOfEmployment'],
                    $item['dateOfDismissal'],
                );
                if ($work === 'меньше 3х месяцев' && $workExp < 0.25) {
                    $resultSet[] = $item;
                } else if ($work === 'до 1 года работы' && $workExp < 1) {
                    $resultSet[] = $item;
                } else if ($work === 'до 3х лет работы' && $workExp < 3) {
                    $resultSet[] = $item;
                } else if (($work === 'свыше 3х лет работы') && $workExp >= 3) {
                    $resultSet[] = $item;
                }
            }
            $result = $resultSet;
            $datasets['label'] .= ' со стажем ' . $work;
        }

        foreach ($result as $key => $item) {
            $reasonData[] = $item['reasonForDismissal'];
            $categoryData[] = $item['categoryOfDismissal'];
        }

        $reasonAndCounts = array_count_values($reasonData);
        $categoryAndCounts = array_count_values($categoryData);

        $resultData = [
            [
                'labels' => $labels,
                'datasets' => [ $datasets ],
            ],
            [
                'labels' => $labels,
                'datasets' => [ $datasets ],
            ]
        ];

        foreach ($reasonAndCounts as $key => $item) {
            $resultData[0]['labels'][] = self::REASON_TYPES[$key];
            $resultData[0]['datasets'][0]['data'][] = $item;
        }

        foreach ($categoryAndCounts as $key => $item) {
            $resultData[1]['labels'][] = self::CATEGORY_TYPE[$key];
            $resultData[1]['datasets'][0]['data'][] = $item;
        }

        $resultData[0]['datasets'][0]['label'] =
            'Причины увольнения ' . ($department ? 'в ' : '') . $resultData[0]['datasets'][0]['label'];

        $resultData[1]['datasets'][0]['label'] =
            'Категории увольнения ' . ($department ? 'в ' : '') . $resultData[1]['datasets'][0]['label'];

        return $resultData;
    }

    public function findAllDepartments(): array
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->distinct(true)
            ->select('e.department')
            ->andWhere('e.status = 3');
        $allData = [];

        /**
         * @var array $result
         */
        $result = $query->getQuery()->getArrayResult();
        foreach ($result as $key => $item) {
            $allData[] = $item['department'];
        }
        return $allData;
    }
}

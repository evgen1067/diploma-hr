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

    public function remove(Employee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param QueryBuilder $query
     * @param array $filter
     * @return QueryBuilder
     * Фильтрация
     */
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

            # Текст в точности
            if (isset($item['type']) && ($item['type'] === 'text_accuracy' || $item['type'] === 'list')) {
                $query->andWhere('e.' . $key . ' = :param' . $key)
                    ->setParameter('param' . $key, $item['value']);
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

    /**
     * @param DateTimeImmutable $dateStart
     * @param DateTimeImmutable|null $dateEnd
     * @return float
     * Получение опыта работы
     */
    private function getWorkExperience(DateTimeImmutable $dateStart, ?DateTimeImmutable $dateEnd = null): float
    {
        if ($dateEnd === null) {
            $dateEnd = new DateTimeImmutable();
        }
        $interval = $dateStart->diff($dateEnd);
        return round(fdiv($interval->days, 365.0), 2);
    }

    /**
     * @param array|null $filter
     * @return array
     * Получение таблицы сотрудников
     */
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

        $resultSet = [];

        $numberFilter = false;

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

            $filterWork = $filter['workExperience'] ?? null;

            # Число не равно
            if (isset($filterWork['type']) && $filterWork['type'] === 'number_equal') {
                $numberFilter = true;
                $value = $filterWork['value'];
                if ($table[$key]['workExperience'] == $value) {
                    $resultSet[] = $table[$key];
                }
            }

            # Число не равно
            else if (isset($filterWork['type']) && $filterWork['type'] === 'number_not_equal') {
                $numberFilter = true;
                $value = $filterWork['value'];
                if ($table[$key]['workExperience'] != $value) {
                    $resultSet[] = $table[$key];
                }
            }

            # Неравенство
            else if (isset($filterWork['type'], $filterWork['valueFrom'], $filterWork['valueTo']) && $filterWork['type'] === 'number_inequality') {
                $numberFilter = true;
                if (isset($filterWork['isStrict'])) {
                    if ($filterWork['valueFrom'] !== '' && $filterWork['valueTo'] === '') {
                        $valueFrom = $filterWork['valueFrom'];
                        if ($table[$key]['workExperience'] > $valueFrom && $filterWork['isStrict'] === true) {
                            $resultSet[] = $table[$key];
                        } else if ($table[$key]['workExperience'] >= $valueFrom && $filterWork['isStrict'] === false) {
                            $resultSet[] = $table[$key];
                        }
                    } else if ($filterWork['valueTo'] !== '' && $filterWork['valueFrom'] === '') {
                        $valueTo = $filterWork['valueTo'];
                        if ($table[$key]['workExperience'] < $valueTo && $filterWork['isStrict'] === true) {
                            $resultSet[] = $table[$key];
                        } else if ($table[$key]['workExperience'] <= $valueTo && $filterWork['isStrict'] === false) {
                            $resultSet[] = $table[$key];
                        }
                    } else if ($filterWork['valueTo'] !== '' && $filterWork['valueFrom'] !== '') {
                        $valueFrom = $filterWork['valueFrom'];
                        $valueTo = $filterWork['valueTo'];
                        if (
                            $table[$key]['workExperience'] < $valueTo &&
                            $table[$key]['workExperience'] > $valueFrom &&
                            $filterWork['isStrict'] === true
                        ) {
                            $resultSet[] = $table[$key];
                        } else if (
                            $table[$key]['workExperience'] <= $valueTo &&
                            $table[$key]['workExperience'] >= $valueFrom &&
                            $filterWork['isStrict'] === false
                        ) {
                            $resultSet[] = $table[$key];
                        }
                    }
                }
            }
        }
        if ($numberFilter) {
            return $resultSet;
        }

        return $table;
    }

    /**
     * @param int $id
     * @return array
     * Данные по отдельному сотруднику в виде массива
     */
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

    /**
     * @param $department
     * @param $valueTo
     * @param $valueFrom
     * @param $work
     * @return array|array[]
     * Данные по увольнениям
     */
    public function findDataForLayoffsChart($department, $valueTo, $valueFrom, $work): array
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
            $query
                ->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        if ($valueFrom) {
            $query
                ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
                ->setParameter('dateOfDismissal1', $valueFrom)
                ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
                ->setParameter('dateOfDismissal2', $valueTo);
            $datasets['label'] .= ' за период с ' . $valueFrom->format('d.m.Y') . ' по ' . $valueTo->format('d.m.Y');
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

    /**
     * @return array
     * Список отделов
     */
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

    public function findDataLayoffsChart($valueTo, $valueFrom): array
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->select('count(e.dateOfDismissal) as count')
            ->addSelect('e.department')
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->groupBy('e.department');
        $result = $query->getQuery()->getArrayResult();

        $labels = [];
        $datasets = [
            'label' => 'Количество увольнений',
            'backgroundColor' => '#2191FB',
            'borderWidth' => 1,
            'data' => []
        ];

        foreach ($result as $key => $item) {
            $labels[] = $item['department'];
            $datasets['data'][] = $item['count'];
        }

        # график уволенных по отделу
        $departmentChart = [
            'labels' => $labels,
            'datasets' => [$datasets],
        ];

        $query = $this->createQueryBuilder('e');
        $query
            ->select('count(e.dateOfDismissal) as count')
            ->addSelect('e.position')
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->groupBy('e.position');
        $result = $query->getQuery()->getArrayResult();

        $labels = [];
        $datasets = [
            'label' => 'Количество увольнений',
            'backgroundColor' => '#8CDEDC',
            'borderWidth' => 1,
            'data' => []
        ];

        foreach ($result as $key => $item) {
            $labels[] = $item['position'];
            $datasets['data'][] = $item['count'];
        }

        # график уволенных по должностям
        $positionChart = [
            'labels' => $labels,
            'datasets' => [$datasets],
        ];

        $query = $this->createQueryBuilder('e');
        $query
            ->select('e')
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo);
        /**
         * @var array $result
         */
        $result = $query->getQuery()->getArrayResult();

        # всего уволено
        $totalDismissed = count($result);

        # средний стаж работы
        $avgWorkExp = 0;

        $labels = ['Количество увольнений'];
        $datasets = [
            [
                'label' => 'Менее 3х месяцев',
                'backgroundColor' => '#329F5B',
                'borderWidth' => 1,
                'data' => [0]
            ],
            [
                'label' => 'до 1 года работы',
                'backgroundColor' => '#8380B6',
                'borderWidth' => 1,
                'data' => [0]
            ],
            [
                'label' => 'до 3х лет работы',
                'backgroundColor' => '#177E89',
                'borderWidth' => 1,
                'data' => [0]
            ],
            [
                'label' => 'свыше 3х лет работы',
                'backgroundColor' => '#F40076',
                'borderWidth' => 1,
                'data' => [0]
            ],
        ];

        foreach ($result as $key => $item) {
            $workExp = $this->getWorkExperience(
                $item['dateOfEmployment'],
                $item['dateOfDismissal'],
            );

            if ($workExp < 0.25) {
                $datasets[0]['data'][0]++;
            } else if ($workExp < 1) {
                $datasets[1]['data'][0]++;
            } else if ($workExp < 3) {
                $datasets[2]['data'][0]++;
            } else {
                $datasets[3]['data'][0]++;
            }
            $avgWorkExp += $workExp;
        }

        $avgWorkExp = fdiv($avgWorkExp, $totalDismissed);

        $workExpChart = [
            'labels' => $labels,
            'datasets' => $datasets,
        ];

        $query = $this->createQueryBuilder('e');
        $query
            ->select('count(e.categoryOfDismissal) as count')
            ->addSelect('e.categoryOfDismissal')
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->groupBy('e.categoryOfDismissal');

        $result = $query->getQuery()->getArrayResult();

        $labels = ['Количество увольнений'];
        $datasets = [];

        foreach ($result as $key => $item) {
            $datasets[] = [
                'label' => self::CATEGORY_TYPE[$item['categoryOfDismissal']],
                'backgroundColor' => self::COLORS[$key],
                'borderWidth' => 1,
                'data' => [$item['count']]
            ];
        }

        # график уволенных по категориям
        $categoryChart = [
            'labels' => $labels,
            'datasets' => $datasets,
        ];

        $query = $this->createQueryBuilder('e');
        $query
            ->select('count(e.categoryOfDismissal) as count')
            ->addSelect('e.reasonForDismissal')
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->groupBy('e.reasonForDismissal');

        $result = $query->getQuery()->getArrayResult();

        $labels = [];
        $datasets = [
            'label' => 'Количество увольнений',
            'backgroundColor' => self::COLORS,
            'borderWidth' => 1,
            'data' => []
        ];

        foreach ($result as $key => $item) {
            $labels[] = self::REASON_TYPES[$item['reasonForDismissal']];
            $datasets['data'][] = $item['count'];
        }

        # график уволенных по причинам
        $reasonChart = [
            'labels' => $labels,
            'datasets' => [$datasets],
        ];

        return [
            'totalDismissed' => $totalDismissed, # кол-во уволенных
            'avgWorkExp' => round($avgWorkExp, 2), # средний стаж
            'departmentChart' => $departmentChart, # общий график по отделам
            'positionChart' => $positionChart, # общий график по должностям
            'workExpChart' => $workExpChart, # общий график по должностям
            'categoryChart' => $categoryChart, # общий график по категориям
            'reasonChart' => $reasonChart, # общий график по причинам
        ];
    }

    public function findDataForTurnoverRates($valueTo, $valueFrom, $department): array
    {
        $labels = [];
        $datasets = [
            'label' => 'Списочная численность',
            'backgroundColor' => '#2191FB',
            'borderWidth' => 1,
            'data' => [],
        ];

        if ($department) {
            $datasets['label'] .= ' в ' . $department;
        } else {
            $datasets['label'] .= ' по всей компании';
        }

        $datasets['label'] .= ' за период с ' . $valueFrom->format('d.m.Y') . ' по ' . $valueTo->format('d.m.Y');

        # количество сотрудников
        
        $query = $this->createQueryBuilder('e');
        $query
            ->select('e')
            ->andWhere('e.status != 3');
        if ($department) {
            $query
                ->andWhere('e.department = :department')
                ->setParameter('department', $department);
        }
        $numberOfEmployees = count($query->getQuery()->getArrayResult());

        # декрет
        
        $query->andWhere('e.status = 2');
        $numberOfDecreeEmployees = count($query->getQuery()->getArrayResult());

        # принято
        
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfEmployment >= :dateOfEmployment1')
            ->setParameter('dateOfEmployment1', $valueFrom)
            ->andWhere('e.dateOfEmployment <= :dateOfEmployment2')
            ->setParameter('dateOfEmployment2', $valueTo);
        if ($department) {
            $query
                ->andWhere('e.department = :department')
                ->setParameter('department', $department);
        }
        $numberOfAcceptedEmployees = count($query->getQuery()->getArrayResult());

        # уволено
        
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo);
        if ($department) {
            $query
                ->andWhere('e.department = :department')
                ->setParameter('department', $department);
        }
        $numberOfDismissedEmployees = count($query->getQuery()->getArrayResult());

        # Среднесписочная численность

        $periodEndDate = $valueTo;

        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo);
        if ($department) {
            $query
                ->andWhere('e.department = :department')
                ->setParameter('department', $department);
        }
        $result = $query->getQuery()->getArrayResult();

        $numberOfAverageEmployees = 0;

        $numberOfDays = 0;

        $constNumberOfEmployees = $numberOfEmployees - $numberOfDecreeEmployees;

        while ($periodEndDate >= $valueFrom) {
            $numberOfDays++;
            $currentIncrement = $constNumberOfEmployees;
            # пробег по уволенным сотрудникам
            foreach ($result as $item) {
                if ($item['dateOfDismissal'] > $periodEndDate) {
                    $currentIncrement++;
                }
            }
            $numberOfAverageEmployees += $currentIncrement;
            $labels[] = $periodEndDate->format('d.m.Y');
            $datasets['data'][] = $currentIncrement;
            $periodEndDate = $periodEndDate->modify('-1 day');
        }
        $datasets['data'] = array_reverse($datasets['data']);

        $numberOfAverageEmployees = round(fdiv($numberOfAverageEmployees, $numberOfDays), 3);

        # коэффициент текучести

        $turnoverRatio = round(fdiv($numberOfDismissedEmployees, $numberOfAverageEmployees), 3);

        # кол-во уволенных по собств желанию

        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo);
        if ($department) {
            $query
                ->andWhere('e.department = :department')
                ->setParameter('department', $department);
        }

        $numberVoluntarily = count($query->getQuery()->getArrayResult());

        # кол-во уволенных по принудительно

        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->andWhere('e.categoryOfDismissal = 2');
        if ($department) {
            $query
                ->andWhere('e.department = :department')
                ->setParameter('department', $department);
        }
        $numberForced = count($query->getQuery()->getArrayResult());

        # кол-во уволенных нежелательно

        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->andWhere('e.categoryOfDismissal = 3');
        if ($department) {
            $query
                ->andWhere('e.department = :department')
                ->setParameter('department', $department);
        }
        $numberUndesirable = count($query->getQuery()->getArrayResult());

        # коэффициент добровольной текучести

        $turnoverVoluntarilyRatio = round(fdiv($numberVoluntarily, $numberOfAverageEmployees), 3);

        # коэффициент принудительной текучести

        $turnoverForcedRatio = round(fdiv($numberForced, $numberOfAverageEmployees), 3);

        # коэффициент нежелательной текучести

        $turnoverUndesirableRatio = round(fdiv($numberUndesirable, $numberOfAverageEmployees), 3);

        return [
            'totalNumber' => $numberOfEmployees,
            'acceptedNumber' => $numberOfAcceptedEmployees,
            'decreeNumber' => $numberOfDecreeEmployees,
            'dismissedNumber' => $numberOfDismissedEmployees,
            'averageNumber' => $numberOfAverageEmployees,
            'averageNumberDataChart' => [
                'labels' => array_reverse($labels),
                'datasets' => [$datasets],
            ],
            'turnoverRatio' => $turnoverRatio,
            'numberVoluntarily' => $numberVoluntarily,
            'turnoverVoluntarilyRatio' => $turnoverVoluntarilyRatio,
            'numberForced' => $numberForced,
            'turnoverForcedRatio' => $turnoverForcedRatio,
            'numberUndesirable' => $numberUndesirable,
            'turnoverUndesirableRatio' => $turnoverUndesirableRatio,
        ];
    }
}

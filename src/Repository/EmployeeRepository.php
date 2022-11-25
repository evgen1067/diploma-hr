<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
     *               Получение таблицы сотрудников
     */
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

            // Число не равно
            if (isset($filterWork['type']) && 'number_equal' === $filterWork['type']) {
                $numberFilter = true;
                $value = $filterWork['value'];
                if ($table[$key]['workExperience'] == $value) {
                    $resultSet[] = $table[$key];
                }
            } // Число не равно
            elseif (isset($filterWork['type']) && 'number_not_equal' === $filterWork['type']) {
                $numberFilter = true;
                $value = $filterWork['value'];
                if ($table[$key]['workExperience'] != $value) {
                    $resultSet[] = $table[$key];
                }
            } // Неравенство
            elseif (isset($filterWork['type'], $filterWork['valueFrom'], $filterWork['valueTo']) && 'number_inequality' === $filterWork['type']) {
                $numberFilter = true;
                if (isset($filterWork['isStrict'])) {
                    if ('' !== $filterWork['valueFrom'] && '' === $filterWork['valueTo']) {
                        $valueFrom = $filterWork['valueFrom'];
                        if ($table[$key]['workExperience'] > $valueFrom && true === $filterWork['isStrict']) {
                            $resultSet[] = $table[$key];
                        } elseif ($table[$key]['workExperience'] >= $valueFrom && false === $filterWork['isStrict']) {
                            $resultSet[] = $table[$key];
                        }
                    } elseif ('' !== $filterWork['valueTo'] && '' === $filterWork['valueFrom']) {
                        $valueTo = $filterWork['valueTo'];
                        if ($table[$key]['workExperience'] < $valueTo && true === $filterWork['isStrict']) {
                            $resultSet[] = $table[$key];
                        } elseif ($table[$key]['workExperience'] <= $valueTo && false === $filterWork['isStrict']) {
                            $resultSet[] = $table[$key];
                        }
                    } elseif ('' !== $filterWork['valueTo'] && '' !== $filterWork['valueFrom']) {
                        $valueFrom = $filterWork['valueFrom'];
                        $valueTo = $filterWork['valueTo'];
                        if (
                            $table[$key]['workExperience'] < $valueTo &&
                            $table[$key]['workExperience'] > $valueFrom &&
                            true === $filterWork['isStrict']
                        ) {
                            $resultSet[] = $table[$key];
                        } elseif (
                            $table[$key]['workExperience'] <= $valueTo &&
                            $table[$key]['workExperience'] >= $valueFrom &&
                            false === $filterWork['isStrict']
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
     * @return array
     *               Данные по отдельному сотруднику в виде массива
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

    public function findDataLayoffsChart(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): array
    {
        $departmentChart = $this->getTotalDismissedByDepartment($valueTo, $valueFrom);

        $positionChart = $this->getTotalDismissedByPosition($valueTo, $valueFrom);

        $workExpChart = $this->getTotalDismissedByWorkExperience($valueTo, $valueFrom);

        $totalDismissed = $this->getTotalDismissedEmployees($valueTo, $valueFrom);

        $avgWorkExp = $this->getAverageWorkExperience($valueTo, $valueFrom);

        // график уволенных по категориям
        $categoryChart = $this->getTotalDismissedByCategory($valueTo, $valueFrom);

        $reasonChart = $this->getTotalDismissedByReason($valueTo, $valueFrom);

        return [
            'totalDismissed' => $totalDismissed, // кол-во уволенных
            'avgWorkExp' => round($avgWorkExp, 2), // средний стаж
            'departmentChart' => $departmentChart, // общий график по отделам
            'positionChart' => $positionChart, // общий график по должностям
            'workExpChart' => $workExpChart, // общий график по должностям
            'categoryChart' => $categoryChart, // общий график по категориям
            'reasonChart' => $reasonChart, // общий график по причинам
        ];
    }

    public function findDataForTurnoverRates(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): array
    {
        // количество сотрудников
        $numberOfEmployees = $this->getTotalNumberOfEmployees($valueTo);

        // декрет
        $numberOfDecreeEmployees = $this->getTotalNumberOfDecreeEmployees();

        // принято
        $numberOfAcceptedEmployees = $this->getTotalNumberOfAcceptedEmployees($valueTo, $valueFrom);

        // уволено
        $numberOfDismissedEmployees = $this->getTotalDismissedEmployees($valueTo, $valueFrom);

        $constNumberOfEmployees = $numberOfEmployees - $numberOfDecreeEmployees;

        $result = $this->getNumberOfAverageEmployees($valueTo, $valueFrom, $constNumberOfEmployees);

        // среднесписочная численность
        $numberOfAverageEmployees = $result['number'];

        // численность на расчетный период
        $averageNumberDataChart = $result['chart'];

        // коэффициент текучести
        $turnoverRatio = round(fdiv($numberOfDismissedEmployees, $numberOfAverageEmployees) * 100, 3);

        // кол-во уволенных по собств желанию
        $numberVoluntarily = $this->getNumberVoluntarilyDismissed($valueTo, $valueFrom);

        // кол-во уволенных по принудительно
        $numberForced = $this->getNumberForcedDismissed($valueTo, $valueFrom);

        // кол-во уволенных нежелательно
        $numberUndesirable = $this->getNumberUndesirableDismissed($valueTo, $valueFrom);

        // коэффициент добровольной текучести
        $turnoverVoluntarilyRatio = round(fdiv($numberVoluntarily, $numberOfAverageEmployees) * 100, 3);

        // коэффициент принудительной текучести
        $turnoverForcedRatio = round(fdiv($numberForced, $numberOfAverageEmployees) * 100, 3);

        // коэффициент нежелательной текучести
        $turnoverUndesirableRatio = round(fdiv($numberUndesirable, $numberOfAverageEmployees) * 100, 3);

        $labels = ['Коэф. текучести'];
        $datasets = [
            [
                'label' => 'Коэффициент текучести',
                'backgroundColor' => self::COLORS[0],
                'borderWidth' => 1,
                'data' => [$turnoverRatio],
            ],
            [
                'label' => 'Коэффициент добровольной текучести',
                'backgroundColor' => self::COLORS[1],
                'borderWidth' => 1,
                'data' => [$turnoverVoluntarilyRatio],
            ],
            [
                'label' => 'Коэффициент принудительной текучести',
                'backgroundColor' => self::COLORS[2],
                'borderWidth' => 1,
                'data' => [$turnoverForcedRatio],
            ],
            [
                'label' => 'Коэффициент нежелательной текучести',
                'backgroundColor' => self::COLORS[3],
                'borderWidth' => 1,
                'data' => [$turnoverUndesirableRatio],
            ],
        ];

        $turnoverChart = [
            'labels' => $labels,
            'datasets' => $datasets,
        ];

        return [
            'totalNumber' => $numberOfEmployees,
            'acceptedNumber' => $numberOfAcceptedEmployees,
            'decreeNumber' => $numberOfDecreeEmployees,
            'dismissedNumber' => $numberOfDismissedEmployees,
            'averageNumber' => $numberOfAverageEmployees,
            'averageNumberDataChart' => $averageNumberDataChart,
            'turnoverChart' => $turnoverChart,
        ];
    }

    /**
     * @return QueryBuilder
     *                      Фильтрация
     */
    private function buildQuery(QueryBuilder $query, array $filter): QueryBuilder
    {
        foreach ($filter as $key => $item) {
            // текст содержит
            if (isset($item['type']) && 'text_contains' === $item['type']) {
                $query->andWhere($query->expr()->like('LOWER(e.'.$key.')', ':param'.$key))
                    ->setParameter('param'.$key, '%'.mb_strtolower($item['value']).'%');
            }

            // текст не содержит
            if (isset($item['type']) && 'text_not_contains' === $item['type']) {
                $query->andWhere($query->expr()->notLike('LOWER(e.'.$key.')', ':param'.$key))
                    ->setParameter('param'.$key, '%'.mb_strtolower($item['value']).'%');
            }

            // начинается с
            if (isset($item['type']) && 'text_start' === $item['type']) {
                $query->andWhere('e.'.$key.' LIKE :param'.$key)
                    ->setParameter('param'.$key, $item['value'].'%');
            }

            // заканчивается на
            if (isset($item['type']) && 'text_end' === $item['type']) {
                $query->andWhere('e.'.$key.' LIKE :param'.$key)
                    ->setParameter('param'.$key, '%'.$item['value']);
            }

            // Текст в точности
            if (isset($item['type']) && ('text_accuracy' === $item['type'] || 'list' === $item['type'])) {
                $query->andWhere('e.'.$key.' = :param'.$key)
                    ->setParameter('param'.$key, $item['value']);
            }

            if (isset($item['type']) && 'date_day' === $item['type']) {
                $query->andWhere('e.'.$key.' = :param'.$key)
                    ->setParameter(
                        'param'.$key,
                        \DateTimeImmutable::createFromFormat('d.m.Y', $item['value'])
                    );
            }

            if (isset($item['type']) && 'date_before' === $item['type']) {
                $query->andWhere('e.'.$key.' < :param'.$key)
                    ->setParameter(
                        'param'.$key,
                        \DateTimeImmutable::createFromFormat('d.m.Y', $item['value'])
                    );
            }

            if (isset($item['type']) && 'date_after' === $item['type']) {
                $query->andWhere('e.'.$key.' > :param'.$key)
                    ->setParameter(
                        'param'.$key,
                        \DateTimeImmutable::createFromFormat('d.m.Y', $item['value'])
                    );
            }
        }

        return $query;
    }

    /**
     * @return float
     *               Получение опыта работы
     */
    private function getWorkExperience(\DateTimeImmutable $dateStart, ?\DateTimeImmutable $dateEnd = null): float
    {
        if (null === $dateEnd) {
            $dateEnd = new \DateTimeImmutable();
        }
        $interval = $dateStart->diff($dateEnd);

        return round(fdiv($interval->days, 365.0), 2);
    }

    /**
     * @return array
     *               информация об уволенных сотрудниках по отделам
     */
    private function getTotalDismissedByDepartment(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): array
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
            'data' => [],
        ];

        foreach ($result as $key => $item) {
            $labels[] = $item['department'];
            $datasets['data'][] = $item['count'];
        }

        // график уволенных по отделу
        return [
            'labels' => $labels,
            'datasets' => [$datasets],
        ];
    }

    /**
     * @return array
     *               Информация об уволенных сотрудниках по должностям
     */
    private function getTotalDismissedByPosition(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): array
    {
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
            'data' => [],
        ];

        foreach ($result as $key => $item) {
            $labels[] = $item['position'];
            $datasets['data'][] = $item['count'];
        }

        // график уволенных по должностям
        return [
            'labels' => $labels,
            'datasets' => [$datasets],
        ];
    }

    /**
     * @return array
     *               Информация об уволенных по категориям
     */
    private function getTotalDismissedByCategory(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): array
    {
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
                'data' => [$item['count']],
            ];
        }

        // график уволенных по категориям
        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * @return array
     *               Информация об уволенных по причинам
     */
    private function getTotalDismissedByReason(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): array
    {
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
            'data' => [],
        ];

        foreach ($result as $key => $item) {
            $labels[] = self::REASON_TYPES[$item['reasonForDismissal']];
            $datasets['data'][] = $item['count'];
        }

        // график уволенных по причинам
        return [
            'labels' => $labels,
            'datasets' => [$datasets],
        ];
    }

    /**
     * @return array
     *               Информация об уволенных по стажу работы
     */
    private function getTotalDismissedByWorkExperience(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom
    ): array {
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

        $labels = ['Количество увольнений'];
        $datasets = [
            [
                'label' => 'Менее 3х месяцев',
                'backgroundColor' => '#329F5B',
                'borderWidth' => 1,
                'data' => [0],
            ],
            [
                'label' => 'до 1 года работы',
                'backgroundColor' => '#8380B6',
                'borderWidth' => 1,
                'data' => [0],
            ],
            [
                'label' => 'до 3х лет работы',
                'backgroundColor' => '#177E89',
                'borderWidth' => 1,
                'data' => [0],
            ],
            [
                'label' => 'свыше 3х лет работы',
                'backgroundColor' => '#F40076',
                'borderWidth' => 1,
                'data' => [0],
            ],
        ];

        foreach ($result as $key => $item) {
            $workExp = $this->getWorkExperience(
                $item['dateOfEmployment'],
                $item['dateOfDismissal'],
            );

            if ($workExp < 0.25) {
                ++$datasets[0]['data'][0];
            } elseif ($workExp < 1) {
                ++$datasets[1]['data'][0];
            } elseif ($workExp < 3) {
                ++$datasets[2]['data'][0];
            } else {
                ++$datasets[3]['data'][0];
            }
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * @return int
     *             Общее количество уволенных
     */
    private function getTotalDismissedEmployees(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): int
    {
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

        // всего уволено
        return count($result);
    }

    /**
     * @return float
     *               Средний стаж работы
     */
    private function getAverageWorkExperience(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): float
    {
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

        // всего уволено
        $totalDismissed = count($result);

        // средний стаж работы
        $avgWorkExp = 0;

        foreach ($result as $key => $item) {
            $workExp = $this->getWorkExperience(
                $item['dateOfEmployment'],
                $item['dateOfDismissal'],
            );
            $avgWorkExp += $workExp;
        }

        return fdiv($avgWorkExp, $totalDismissed);
    }

    /**
     * @return int
     *             Количество сотрудников на расчетный период
     */
    private function getTotalNumberOfEmployees(\DateTimeImmutable $valueTo): int
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->select('e')
            ->andWhere('e.status != 3 OR (e.status = 3 AND e.dateOfDismissal >= :dateOfDismissal)')
            ->setParameter('dateOfDismissal', $valueTo);

        return count($query->getQuery()->getArrayResult());
    }

    /**
     * @return int
     *             Количество сотрудников в декрете
     */
    private function getTotalNumberOfDecreeEmployees(): int
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->select('e');
        $query->andWhere('e.status = 2');

        return count($query->getQuery()->getArrayResult());
    }

    /**
     * @return int
     *             Количество принятых сотрудников за расчетный период
     */
    private function getTotalNumberOfAcceptedEmployees(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): int
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfEmployment >= :dateOfEmployment1')
            ->setParameter('dateOfEmployment1', $valueFrom)
            ->andWhere('e.dateOfEmployment <= :dateOfEmployment2')
            ->setParameter('dateOfEmployment2', $valueTo);

        return count($query->getQuery()->getArrayResult());
    }

    /**
     * @return array
     *               Среднесписочная численность и данные по численности на каждый день
     */
    private function getNumberOfAverageEmployees(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        int $constNumberOfEmployees
    ): array {
        $labels = [];
        $datasets = [
            'label' => 'Списочная численность по всей компании',
            'backgroundColor' => '#4056A1',
            'borderWidth' => 1,
            'data' => [],
        ];

        $datasets['label'] .=
            ' за период с '.$valueFrom->format('d.m.Y').' по '.$valueTo->format('d.m.Y');

        // Среднесписочная численность

        $periodEndDate = $valueTo;

        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo);

        $result = $query->getQuery()->getArrayResult();

        $numberOfAverageEmployees = 0;

        $numberOfDays = $valueTo->diff($valueFrom)->days + 1;

        while ($periodEndDate >= $valueFrom) {
            $currentIncrement = $constNumberOfEmployees;
            // пробег по уволенным сотрудникам
            foreach ($result as $item) {
                if ($item['dateOfDismissal'] > $periodEndDate) {
                    ++$currentIncrement;
                }
            }
            $numberOfAverageEmployees += $currentIncrement;
            $labels[] = $periodEndDate->format('d.m.Y');
            $datasets['data'][] = $currentIncrement;
            $periodEndDate = $periodEndDate->modify('-1 day');
        }

        $datasets['data'] = array_reverse($datasets['data']);

        return [
            'number' => round(fdiv($numberOfAverageEmployees, $numberOfDays), 3),
            'chart' => [
                'labels' => $labels,
                'datasets' => [$datasets],
            ],
        ];
    }

    private function getNumberVoluntarilyDismissed(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): int
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->andWhere('e.categoryOfDismissal = 1');

        return count($query->getQuery()->getArrayResult());
    }

    private function getNumberForcedDismissed(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): int
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->andWhere('e.categoryOfDismissal = 2');

        return count($query->getQuery()->getArrayResult());
    }

    private function getNumberUndesirableDismissed(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): int
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->andWhere('e.categoryOfDismissal = 3');

        return count($query->getQuery()->getArrayResult());
    }
}

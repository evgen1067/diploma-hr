<?php

namespace App\Repository;

class LayoffsRepository extends EmployeeRepository
{
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

    /**
     * @return array
     *               информация об уволенных сотрудниках по отделам
     */
    protected function getTotalDismissedByDepartment(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): array
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
    protected function getTotalDismissedByPosition(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom): array
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
    protected function getTotalDismissedByWorkExperience(
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
}

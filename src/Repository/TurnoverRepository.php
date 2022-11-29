<?php

namespace App\Repository;

class TurnoverRepository extends LayoffsRepository
{
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

        $labels = ['К.Т.'];
        $datasets = [
            [
                'label' => 'Коэффициент текучести (К.Т.)',
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

        $turnoverChartByWE = $this->getTurnoverByWorkExperience($valueTo, $valueFrom, $numberOfAverageEmployees);

        $turnoverChartByDep = $this->getTurnoverByDepartment($valueTo, $valueFrom, $numberOfAverageEmployees);

        $turnoverChartByPos = $this->getTurnoverByPosition($valueTo, $valueFrom, $numberOfAverageEmployees);

        return [
            'totalNumber' => $numberOfEmployees,
            'acceptedNumber' => $numberOfAcceptedEmployees,
            'decreeNumber' => $numberOfDecreeEmployees,
            'dismissedNumber' => $numberOfDismissedEmployees,
            'averageNumber' => $numberOfAverageEmployees,
            'averageNumberDataChart' => $averageNumberDataChart,
            'turnoverChart' => $turnoverChart,
            'turnoverChartByWe' => $turnoverChartByWE,
            'turnoverChartByDep' => $turnoverChartByDep,
            'turnoverChartByPos' => $turnoverChartByPos,
        ];
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

    /**
     * @return array
     *               Информация об показателях текучести по стажу работы
     */
    private function getTurnoverByWorkExperience(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        float $numberOfAverageEmployees
    ): array {
        $turnoverByWorkExperience = $this->getTotalDismissedByWorkExperience($valueTo, $valueFrom);
        $turnoverByWorkExperience['labels'] = ['К.T. по стажу работы'];
        $datasets = $turnoverByWorkExperience['datasets'];
        foreach ($datasets as $i => $iValue) {
            $datasets[$i]['data'][0] = round(fdiv($iValue['data'][0], $numberOfAverageEmployees) * 100, 3);
        }
        $turnoverByWorkExperience['datasets'] = $datasets;

        return $turnoverByWorkExperience;
    }

    /**
     * @return array
     *               Информация об показателях текучести по отделам
     */
    private function getTurnoverByDepartment(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        float $numberOfAverageEmployees
    ): array {
        $turnoverByDepartment = $this->getTotalDismissedByDepartment($valueTo, $valueFrom);
        $labels = $turnoverByDepartment['labels'];
        $datasets = $turnoverByDepartment['datasets'][0]['data'];
        foreach ($labels as $i => $iValue) {
            $labels[$i] = 'К.T. ('.$iValue.')';
        }
        foreach ($datasets as $i => $iValue) {
            $datasets[$i] = round(fdiv($iValue, $numberOfAverageEmployees) * 100, 3);
        }
        $turnoverByDepartment['datasets'][0]['data'] = $datasets;
        $turnoverByDepartment['datasets'][0]['label'] = ['К.T. по отделам'];
        $turnoverByDepartment['labels'] = $labels;

        return $turnoverByDepartment;
    }

    /**
     * @return array
     *               Информация об показателях текучести по должностям
     */
    private function getTurnoverByPosition(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        float $numberOfAverageEmployees
    ): array {
        $turnoverByPos = $this->getTotalDismissedByPosition($valueTo, $valueFrom);
        $labels = $turnoverByPos['labels'];
        $datasets = $turnoverByPos['datasets'][0]['data'];
        foreach ($labels as $i => $iValue) {
            $labels[$i] = 'К.T. ('.$iValue.')';
        }
        foreach ($datasets as $i => $iValue) {
            $datasets[$i] = round(fdiv($iValue, $numberOfAverageEmployees) * 100, 3);
        }
        $turnoverByPos['datasets'][0]['data'] = $datasets;
        $turnoverByPos['datasets'][0]['label'] = ['К.T. по должностям'];
        $turnoverByPos['labels'] = $labels;

        return $turnoverByPos;
    }
}

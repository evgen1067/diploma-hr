<?php

namespace App\Repository;

class TurnoverRepository extends LayoffsRepository
{
    public function findDataForTurnoverRates(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom, string|null $department): array
    {
        // число сотрудников на конец временного периода
        $numberOfEmployees = $this->getTotalNumberOfEmployees($valueTo, $department);

        // число принятых сотрудников на временной период
        $numberOfAcceptedEmployees = $this->getTotalNumberOfAcceptedEmployees($valueTo, $valueFrom, $department);

        // число уволенных сотрудников на временной период
        $numberOfDismissedEmployees = $this->getTotalDismissedEmployees($valueTo, $valueFrom, $department);

        // кол-во уволенных по собств желанию
        $numberVoluntarily = $this->getNumberVoluntarilyDismissed($valueTo, $valueFrom, $department);

        // кол-во уволенных по принудительно
        $numberForced = $this->getNumberForcedDismissed($valueTo, $valueFrom, $department);

        // кол-во уволенных нежелательно
        $numberUndesirable = $this->getNumberUndesirableDismissed($valueTo, $valueFrom, $department);

        // число сотрудников в декрете на временной период
        $numberOfDecreeEmployees = $this->getTotalNumberOfDecreeEmployees($department);

        // постоянная разница в количестве сотрудников
        $permanentDifferenceOfEmployees =
            $this->getTotalNumberOfEmployees($valueTo, null) - $this->getTotalNumberOfDecreeEmployees(null);

        $result = $this->getNumberOfAverageEmployees($valueTo, $valueFrom, $permanentDifferenceOfEmployees);

        // среднесписочная численность
        $numberOfAverageEmployees = $result['avg'];

        // численность на расчетный период
        $averageNumberDataChart = $result['chartData'];

        // коэффициент текучести
        $turnoverRatio = round(fdiv($numberOfDismissedEmployees, $numberOfAverageEmployees) * 100, 3);

        // коэффициент добровольной текучести
        $turnoverVoluntarilyRatio = round(fdiv($numberVoluntarily, $numberOfAverageEmployees) * 100, 3);

        // коэффициент принудительной текучести
        $turnoverForcedRatio = round(fdiv($numberForced, $numberOfAverageEmployees) * 100, 3);

        // коэффициент нежелательной текучести
        $turnoverUndesirableRatio = round(fdiv($numberUndesirable, $numberOfAverageEmployees) * 100, 3);

        $turnoverChart = [
            ['key' => 'Коэффициент текучести', 'value' => $turnoverRatio],
            ['key' => 'Коэффициент добровольной текучести', 'value' => $turnoverVoluntarilyRatio],
            ['key' => 'Коэффициент принудительной текучести', 'value' => $turnoverForcedRatio],
            ['key' => 'Коэффициент нежелательной текучести', 'value' => $turnoverUndesirableRatio],
        ];

        $workExperienceChart = self::getTotalDismissedByWorkExperience($valueTo, $valueFrom, $department);
        for ($i = 0; $i < count($workExperienceChart); ++$i) {
            $workExperienceChart[$i]['value'] =
                round(fdiv($workExperienceChart[$i]['value'], $numberOfAverageEmployees) * 100, 3);
        }

        $departmentChart = self::getTotalDismissedByPosition($valueTo, $valueFrom);
        for ($i = 0; $i < count($departmentChart); ++$i) {
            $departmentChart[$i]['value'] =
                round(fdiv($departmentChart[$i]['value'], $numberOfAverageEmployees) * 100, 3);
        }

        $positionChart = self::getTotalDismissedByPosition($valueTo, $valueFrom);
        for ($i = 0; $i < count($positionChart); ++$i) {
            $positionChart[$i]['value'] =
                round(fdiv($positionChart[$i]['value'], $numberOfAverageEmployees) * 100, 3);
        }

        return [
            'totalNumber' => $numberOfEmployees,
            'acceptedNumber' => $numberOfAcceptedEmployees,
            'decreeNumber' => $numberOfDecreeEmployees,
            'dismissedNumber' => $numberOfDismissedEmployees,
            'averageNumber' => $numberOfAverageEmployees,
            'turnoverChart' => $turnoverChart,
            'averageNumberDataChart' => $averageNumberDataChart,
            'workExperienceChart' => $workExperienceChart,
            'departmentChart' => $departmentChart,
            'positionChart' => $positionChart,
        ];
    }

    /**
     * @return int
     *             Количество сотрудников на расчетный период
     */
    private function getTotalNumberOfEmployees(\DateTimeImmutable $valueTo, string|null $department): int
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->select('e')
            ->andWhere('e.status != 3 OR (e.status = 3 AND e.dateOfDismissal >= :dateOfDismissal)')
            ->setParameter('dateOfDismissal', $valueTo);

        if (!is_null($department)) {
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        return count($query->getQuery()->getArrayResult());
    }

    /**
     * @return int
     *             Количество сотрудников в декрете
     */
    private function getTotalNumberOfDecreeEmployees(string|null $department): int
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->select('e');
        $query->andWhere('e.status = 2');

        if (!is_null($department)) {
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        return count($query->getQuery()->getArrayResult());
    }

    /**
     * @return int
     *             Количество принятых сотрудников за расчетный период
     */
    private function getTotalNumberOfAcceptedEmployees(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom, string|null $department): int
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfEmployment >= :dateOfEmployment1')
            ->setParameter('dateOfEmployment1', $valueFrom)
            ->andWhere('e.dateOfEmployment <= :dateOfEmployment2')
            ->setParameter('dateOfEmployment2', $valueTo);

        if (!is_null($department)) {
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

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
        $data = [];

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
            $data[] = [
                'key' => $periodEndDate->format('d.m.Y'),
                'value' => $currentIncrement,
            ];
            $periodEndDate = $periodEndDate->modify('-1 day');
        }

        $data = array_reverse($data);

        $datasets['data'] = $data;

        return [
            'avg' => round(fdiv($numberOfAverageEmployees, $numberOfDays), 3),
            'chartData' => $data,
        ];
    }

    private function getNumberVoluntarilyDismissed(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        string|null $department
    ): int {
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->andWhere('e.categoryOfDismissal = 1');

        if (!is_null($department)) {
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        return count($query->getQuery()->getArrayResult());
    }

    private function getNumberForcedDismissed(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        string|null $department
    ): int {
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->andWhere('e.categoryOfDismissal = 2');

        if (!is_null($department)) {
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        return count($query->getQuery()->getArrayResult());
    }

    private function getNumberUndesirableDismissed(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        string|null $department
    ): int {
        $query = $this->createQueryBuilder('e');
        $query
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->andWhere('e.categoryOfDismissal = 3');

        if (!is_null($department)) {
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        return count($query->getQuery()->getArrayResult());
    }
}

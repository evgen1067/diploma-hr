<?php

namespace App\Repository;

class LayoffsRepository extends EmployeeRepository
{
    public function findDataLayoffsChart(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom, string|null $department): array
    {
        // общее число увольнений
        $totalDismissed = $this->getTotalDismissedEmployees($valueTo, $valueFrom, $department);
        // средний стаж работы
        $avgWorkExp = $this->getAverageWorkExperience($valueTo, $valueFrom, $department);
        // данные по увольнениям в зависимости от причин
        $reasonChart = $this->getTotalDismissedByReason($valueTo, $valueFrom, $department);
        // данные по увольнениям в зависимости от отдела
        $departmentChart = $this->getTotalDismissedByDepartment($valueTo, $valueFrom);
        // данные по увольнениям в зависимости от должности
        $positionChart = $this->getTotalDismissedByPosition($valueTo, $valueFrom);
        // данные по увольнениям в зависимости от стажа работы
        $workExpChart = $this->getTotalDismissedByWorkExperience($valueTo, $valueFrom, $department);
        // данные по увольнениям в зависимости от категории увольнения
        $categoryChart = $this->getTotalDismissedByCategory($valueTo, $valueFrom, $department);
        // данные по увольнениям в зависимости от гендера
        $genderChart = $this->getTotalDismissedEmployeesByGender($valueTo, $valueFrom, $department);

        return [
            'totalDismissed' => $totalDismissed, // кол-во уволенных
            'avgWorkExp' => round($avgWorkExp, 2), // средний стаж
            'departmentChart' => $departmentChart, // общий график по отделам
            'positionChart' => $positionChart, // общий график по должностям
            'workExpChart' => $workExpChart, // общий график по должностям
            'categoryChart' => $categoryChart, // общий график по категориям
            'reasonChart' => $reasonChart, // общий график по причинам
            'genderChart' => $genderChart, // общий график по гендерам
        ];
    }

    /**
     * @param \DateTimeImmutable $valueTo
     * @param \DateTimeImmutable $valueFrom
     * @param string|null $department
     * @return array
     */
    public function getTotalDismissedEmployeesByGender(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom, ?string $department): array
    {
        $query = $this->createQueryBuilder('e');
        $query
            ->select('count(e.dateOfDismissal) as count')
            ->addSelect('e.gender')
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->groupBy('e.gender');
        $result = $query->getQuery()->getArrayResult();

        $data = [];

        foreach ($result as $key => $item) {
            $data[] = [
                'key' => self::GENDER_TYPES[$item['gender']],
                'value' => $item['count'],
            ];
        }

        // график уволенных по гендеру
        return $data;
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

        $data = [];

        foreach ($result as $key => $item) {
            $data[] = [
                'key' => $item['department'],
                'value' => $item['count'],
            ];
        }

        // график уволенных по отделу
        return $data;
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

        $data = [];

        foreach ($result as $key => $item) {
            $data[] = [
                'key' => $item['position'],
                'value' => $item['count'],
            ];
        }

        // график уволенных по должностям
        return $data;
    }

    /**
     * @return array
     *               Информация об уволенных по стажу работы
     */
    protected function getTotalDismissedByWorkExperience(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        string|null $department
    ): array {
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

        $data = [
            ['key' => 'Менее 3х месяцев', 'value' => 0],
            ['key' => 'До 1 года работы', 'value' => 0],
            ['key' => 'До 3х лет работы', 'value' => 0],
            ['key' => 'Свыше 3х лет работы', 'value' => 0],
        ];

        foreach ($result as $key => $item) {
            $workExp = $this->getWorkExperience(
                $item['dateOfEmployment'],
                $item['dateOfDismissal'],
            );

            if ($workExp < 0.25) {
                ++$data[0]['value'];
            } elseif ($workExp < 1) {
                ++$data[1]['value'];
            } elseif ($workExp < 3) {
                ++$data[2]['value'];
            } else {
                ++$data[3]['value'];
            }
        }

        return $data;
    }

    /**
     * @return array
     *               Информация об уволенных по категориям
     */
    private function getTotalDismissedByCategory(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        string|null $department
    ): array {
        $query = $this->createQueryBuilder('e');
        $query
            ->select('count(e.categoryOfDismissal) as count')
            ->addSelect('e.categoryOfDismissal')
            ->andWhere('e.dateOfDismissal >= :dateOfDismissal1')
            ->setParameter('dateOfDismissal1', $valueFrom)
            ->andWhere('e.dateOfDismissal <= :dateOfDismissal2')
            ->setParameter('dateOfDismissal2', $valueTo)
            ->groupBy('e.categoryOfDismissal');

        if (!is_null($department)) {
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        $result = $query->getQuery()->getArrayResult();

        $data = [];

        foreach ($result as $key => $item) {
            $data[] = [
                'key' => self::CATEGORY_TYPE[$item['categoryOfDismissal']],
                'value' => $item['count'],
            ];
        }

        // график уволенных по категориям
        return $data;
    }

    /**
     * @return array
     *               Информация об уволенных по причинам
     */
    private function getTotalDismissedByReason(\DateTimeImmutable $valueTo, \DateTimeImmutable $valueFrom, string|null $department): array
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

        if (!is_null($department)) {
            $query->andWhere('e.department = :depName')
                ->setParameter('depName', $department);
        }

        $result = $query->getQuery()->getArrayResult();

        $data = [];

        foreach ($result as $key => $item) {
            $data[] = [
                'key' => self::REASON_TYPES[$item['reasonForDismissal']],
                'value' => $item['count'],
            ];
        }

        // график уволенных по причинам
        return $data;
    }

    /**
     * @return float
     *               Средний стаж работы
     */
    private function getAverageWorkExperience(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        string|null $department
    ): float {
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

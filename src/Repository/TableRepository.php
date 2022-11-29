<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

class TableRepository extends EmployeeRepository
{
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
            elseif (isset($filterWork['type']) && 'number_inequality' === $filterWork['type']) {
                $numberFilter = true;
                $valueFrom = $filterWork['valueFrom'] ?? '';
                $valueTo = $filterWork['valueTo'] ?? '';
                if (isset($filterWork['isStrict'])) {
                    if ('' !== $valueFrom && '' === $valueTo) {
                        if ($table[$key]['workExperience'] > $valueFrom && true === $filterWork['isStrict']) {
                            $resultSet[] = $table[$key];
                        } elseif ($table[$key]['workExperience'] >= $valueFrom && false === $filterWork['isStrict']) {
                            $resultSet[] = $table[$key];
                        }
                    } elseif ('' !== $valueTo && '' === $valueFrom) {
                        if ($table[$key]['workExperience'] < $valueTo && true === $filterWork['isStrict']) {
                            $resultSet[] = $table[$key];
                        } elseif ($table[$key]['workExperience'] <= $valueTo && false === $filterWork['isStrict']) {
                            $resultSet[] = $table[$key];
                        }
                    } elseif ('' !== $valueTo && '' !== $valueFrom) {
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
            $result['dateOfEmployment'] = $result['dateOfEmployment']->format('Y-m-d');
            if (isset($result['dateOfDismissal'])) {
                $result['dateOfDismissal'] = $result['dateOfDismissal']->format('Y-m-d');
            }
        }

        return $result;
    }

    /**
     * @return QueryBuilder
     *                      Фильтрация
     */
    private function buildQuery(QueryBuilder $query, array $filter): QueryBuilder
    {
        foreach ($filter as $key => $item) {
            if (isset($item['type'])) {
                $type = $item['type'];
                // текст содержит
                if ('text_contains' === $type) {
                    $query->andWhere($query->expr()->like('LOWER(e.'.$key.')', ':param'.$key))
                        ->setParameter('param'.$key, '%'.mb_strtolower($item['value']).'%');
                }

                // текст не содержит
                if ('text_not_contains' === $type) {
                    $query->andWhere($query->expr()->notLike('LOWER(e.'.$key.')', ':param'.$key))
                        ->setParameter('param'.$key, '%'.mb_strtolower($item['value']).'%');
                }

                // начинается с
                if ('text_start' === $type) {
                    $query->andWhere('LOWER(e.'.$key.') LIKE :param'.$key)
                        ->setParameter('param'.$key, mb_strtolower($item['value']).'%');
                }

                // заканчивается на
                if ('text_end' === $type) {
                    $query->andWhere('LOWER(e.'.$key.') LIKE :param'.$key)
                        ->setParameter('param'.$key, '%'.mb_strtolower($item['value']));
                }

                // Текст в точности
                if (('text_accuracy' === $type || 'list' === $type)) {
                    $query->andWhere('e.'.$key.' = :param'.$key)
                        ->setParameter('param'.$key, $item['value']);
                }

                if ('date_day' === $type) {
                    $query->andWhere('e.'.$key.' = :param'.$key)
                        ->setParameter(
                            'param'.$key,
                            \DateTimeImmutable::createFromFormat('d.m.Y', $item['value'])
                        );
                }

                if ('date_before' === $type) {
                    $query->andWhere('e.'.$key.' < :param'.$key)
                        ->setParameter(
                            'param'.$key,
                            \DateTimeImmutable::createFromFormat('d.m.Y', $item['value'])
                        );
                }

                if ('date_after' === $type) {
                    $query->andWhere('e.'.$key.' > :param'.$key)
                        ->setParameter(
                            'param'.$key,
                            \DateTimeImmutable::createFromFormat('d.m.Y', $item['value'])
                        );
                }
            }
        }

        return $query;
    }
}

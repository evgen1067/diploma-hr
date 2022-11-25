<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    private const REVERSE_STATUS_TYPES = [
        'работает' => 1,
        'декрет' => 2,
        'уволен' => 3,
    ];

    private const REVERSE_REASON_TYPES = [
        'не пройден испытательный срок' => 1,
        'проблемы с дисциплиной' => 2,
        'не справлялся с поставленными задачами' => 3,
        'сокращение' => 4,
        'предложение о работе с высокой заработной платой' => 5,
        'потерял ценность' => 6,
        'не видит для себя профессионального развития' => 7,
        'хочет сменить должность/направление' => 8,
        'выгорание' => 9,
        'релокация' => 10,
    ];

    private const REVERSE_CATEGORY_TYPE = [
        'добровольная' => 1,
        'принудительная' => 2,
        'нежелательная' => 3,
    ];

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $csv = array_map('str_getcsv', file(dirname(__DIR__).'/DataFixtures/start_data.csv'));
        array_shift($csv);

        $format = 'd.m.Y';

        foreach ($csv as $i => $iValue) {
            $faker = Faker\Factory::create('ru_RU');
            $employee = new Employee();
            $csv[$i][1] = \DateTimeImmutable::createFromFormat($format, $iValue[1]);
            $csv[$i][5] = self::REVERSE_STATUS_TYPES[$iValue[5]];
            $employee
                ->setFullName($faker->name)
                ->setDateOfEmployment($csv[$i][1])
                ->setDepartment($csv[$i][3])
                ->setPosition($csv[$i][4])
                ->setStatus($csv[$i][5]);
            if (isset($csv[$i][6]) && '' !== $iValue[6]) {
                $csv[$i][6] = \DateTimeImmutable::createFromFormat($format, $iValue[6]);
                $csv[$i][7] = self::REVERSE_REASON_TYPES[$iValue[7]];
                $csv[$i][8] = self::REVERSE_CATEGORY_TYPE[$iValue[8]];
                if (3 === $csv[$i][5]) {
                    $employee
                        ->setDateOfDismissal($csv[$i][6])
                        ->setReasonForDismissal($csv[$i][7])
                        ->setCategoryOfDismissal($csv[$i][8]);
                }
            }
            $manager->persist($employee);
        }

        $manager->flush();
    }
}

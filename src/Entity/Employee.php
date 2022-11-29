<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    private const STATUS_TYPES = [
        1 => 'работает',
        2 => 'декрет',
        3 => 'уволен',
    ];

    private const REVERSE_STATUS_TYPES = [
        'работает' => 1,
        'декрет' => 2,
        'уволен' => 3,
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

    private const CATEGORY_TYPE = [
        1 => 'добровольная',
        2 => 'принудительная',
        3 => 'нежелательная',
    ];

    private const REVERSE_CATEGORY_TYPE = [
        'добровольная' => 1,
        'принудительная' => 2,
        'нежелательная' => 3,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ФИО
    #[ORM\Column(length: 512)]
    private ?string $fullName = null;

    // Дата трудоустройства
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dateOfEmployment = null;

    // Отдел
    #[ORM\Column(length: 255)]
    private ?string $department = null;

    // Должность
    #[ORM\Column(length: 255)]
    private ?string $position = null;

    // Статус
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;

    // Дата увольнения
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateOfDismissal = null;

    // Причина увольнения
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $reasonForDismissal = null;

    // Категория увольнения
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $categoryOfDismissal = null;

    // TODO LEVEL
//    #[ORM\Column(type: Types::SMALLINT)]
//    private ?int $level = null;
//
//    private const LEVEL_TYPES = [
//        1 => 'Junior',
//        2 => 'Middle',
//        3 => 'Senior',
//        4 => 'Lead',
//    ];
//
//    private const REVERSE_LEVEL_TYPES = [
//        'Junior' => 1,
//        'Middle' => 2,
//        'Senior' => 3,
//        'Lead' => 4,
//    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getDateOfEmployment(): ?\DateTimeImmutable
    {
        return $this->dateOfEmployment;
    }

    public function setDateOfEmployment(\DateTimeImmutable $dateOfEmployment): self
    {
        $this->dateOfEmployment = $dateOfEmployment;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getStatus(): ?string
    {
        return self::STATUS_TYPES[$this->status];
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateOfDismissal(): ?\DateTimeImmutable
    {
        return $this->dateOfDismissal;
    }

    public function setDateOfDismissal(?\DateTimeImmutable $dateOfDismissal): self
    {
        $this->dateOfDismissal = $dateOfDismissal;

        return $this;
    }

    public function getReasonForDismissal(): ?int
    {
        return $this->reasonForDismissal;
    }

    public function setReasonForDismissal(?int $reasonForDismissal): self
    {
        $this->reasonForDismissal = $reasonForDismissal;

        return $this;
    }

    public function getCategoryOfDismissal(): ?int
    {
        return $this->categoryOfDismissal;
    }

    public function setCategoryOfDismissal(?int $categoryOfDismissal): self
    {
        $this->categoryOfDismissal = $categoryOfDismissal;

        return $this;
    }

    public function fromJson($dataForCreate): Employee
    {
        $fullName = $dataForCreate['fullName'];
        $dateOfEmployment = $dataForCreate['dateOfEmployment'];
        $department = $dataForCreate['department'];
        $position = $dataForCreate['position'];
        $status = $dataForCreate['status'];

        $dateOfDismissal = $dataForCreate['dateOfDismissal'] ?? null;
        $reasonForDismissal = $dataForCreate['reasonForDismissal'] ?? null;
        $categoryOfDismissal = $dataForCreate['categoryOfDismissal'] ?? null;

        $this
            ->setFullName($fullName)
            ->setDateOfEmployment(\DateTimeImmutable::createFromFormat('Y-m-d', $dateOfEmployment))
            ->setDepartment($department)
            ->setPosition($position)
            ->setStatus($status);

        if (3 === $this->getStatus()) {
            if ($dateOfDismissal) {
                $dateOfDismissal = \DateTimeImmutable::createFromFormat('Y-m-d', $dateOfDismissal);
                $this->setDateOfDismissal($dateOfDismissal);
            }

            if ($reasonForDismissal) {
                $this->setReasonForDismissal($reasonForDismissal);
            }

            if ($categoryOfDismissal) {
                $this->setCategoryOfDismissal($categoryOfDismissal);
            }
        }

        return $this;
    }

//    public function getLevel(): ?string
//    {
//        return self::LEVEL_TYPES[$this->level];
//    }

//    public function setLevel(int $level): self
//    {
//        $this->level = $level;
//
//        return $this;
//    }
}

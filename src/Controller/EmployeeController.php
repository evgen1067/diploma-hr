<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use DateTimeImmutable;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JsonException;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class EmployeeController extends AbstractController
{
    private Serializer $serializer;

    public function __construct() {
        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * @throws JsonException
     */
    #[Route('/employees', name: 'app_employees', methods: ['GET'])]
    public function employees(
        Request $request,
        EmployeeRepository $employeeRepository,
        PaginatorInterface $paginator
    ): JsonResponse
    {
        // номер страницы
        $page = $request->query->get('page') ?: 1;

        // кол-во записей на странице
        $perPage = $request->query->get('perPage') ?: 10;

        // фильтр
        $filter = $request->query->get('filter') ? json_decode($request->query->get('filter'), true, 512, JSON_THROW_ON_ERROR) : null;
        // записи до пагинации
        $tableData = $employeeRepository->getTableData($filter);

        if ($perPage !== 'Все') {
            // пагинация
            $tableData = $paginator->paginate(
                $tableData,
                $page,
                $perPage
            );
            // отбор нужных полей
            $tableData = [
                'items' => $tableData->getItems(),
                'totalCount' => $tableData->getTotalItemCount(),
            ];
        } else {
            $tableData = [
                'items' => $tableData,
                'totalCount' => count($tableData),
            ];
        }

        // сериализуем ответ
        $tableData = $this->serializer->serialize($tableData, 'json');

        // отдаем ответ
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($tableData);
        return $response;
    }

    #[Route('/employees/{id}', name: 'app_employee', methods: ['GET'])]
    public function employee(
        int $id,
        EmployeeRepository $employeeRepository
    ): JsonResponse
    {
        $employee = $employeeRepository->findEmployeeInArray($id);

        $response = new JsonResponse();

        if (!$employee) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $this->serializer->serialize([
                'message' => 'Сотрудник не найден'
            ], 'json');
            $response->setContent($content);
            return $response;
        }

        $response->setStatusCode(Response::HTTP_OK);
        $content = $this->serializer->serialize($employee, 'json');
        $response->setContent($content);
        return $response;
    }

    /**
     * @throws JsonException
     */
    #[Route('/employees/new', name: 'app_employees_new', methods: ['POST'])]
    public function new(
        Request $request,
        EmployeeRepository $employeeRepository
    ): JsonResponse
    {
        $dataForCreate = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $response = new JsonResponse();

        $content = [];

        $fullName = $dataForCreate['fullName'];
        $dateOfEmployment = $dataForCreate['dateOfEmployment'];
        $department = $dataForCreate['department'];
        $position = $dataForCreate['position'];
        $status = $dataForCreate['status'];

        $dateOfDismissal = $dataForCreate['dateOfDismissal'];
        $reasonForDismissal = $dataForCreate['reasonForDismissal'];
        $categoryOfDismissal = $dataForCreate['categoryOfDismissal'];

        $content = $this->validEmployee($fullName, $dateOfEmployment, $department, $position, $status);

        if (count($content) > 0) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent($this->serializer->serialize(
                $content, 'json'
            ));
            return $response;
        }

        $employee = new Employee();
        $employee
            ->setFullName($fullName)
            ->setDateOfEmployment(\DateTimeImmutable::createFromFormat('Y-m-d', $dateOfEmployment))
            ->setDepartment($department)
            ->setPosition($position)
            ->setStatus($status);

        if ($employee->getStatus() === 'уволен') {
            if ($dateOfDismissal) {
                $dateOfDismissal = DateTimeImmutable::createFromFormat('Y-m-d', $dateOfDismissal);
                $employee->setDateOfDismissal($dateOfDismissal);
            }

            if ($reasonForDismissal) {
                $employee->setReasonForDismissal($reasonForDismissal);
            }

            if ($categoryOfDismissal) {
                $employee->setCategoryOfDismissal($categoryOfDismissal);
            }
        }

        $employeeRepository->save($employee, true);

        $response->setStatusCode(Response::HTTP_CREATED);
        $response->setContent($this->serializer->serialize(['message' => 'Сотрудник успешно создан'], 'json'));
        return $response;
    }

    /**
     * @throws JsonException
     */
    #[Route('/employees/{id}', name: 'app_employees_put', methods: ['PUT'])]
    public function edit(
        int $id,
        Request $request,
        EmployeeRepository $employeeRepository
    ): JsonResponse
    {
        $dataForCreate = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $response = new JsonResponse();

        $content = [];

        $fullName = $dataForCreate['fullName'];
        $dateOfEmployment = $dataForCreate['dateOfEmployment'];
        $department = $dataForCreate['department'];
        $position = $dataForCreate['position'];
        $status = $dataForCreate['status'];

        $dateOfDismissal = $dataForCreate['dateOfDismissal'] ?? null;
        $reasonForDismissal = $dataForCreate['reasonForDismissal'] ?? null;
        $categoryOfDismissal = $dataForCreate['categoryOfDismissal'] ?? null;

        $content = $this->validEmployee($fullName, $dateOfEmployment, $department, $position, $status);

        if (count($content) > 0) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent($this->serializer->serialize(
                $content, 'json'
            ));
            return $response;
        }

        $employee = $employeeRepository->find($id);

        if (!$employee) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $this->serializer->serialize([
                'message' => 'Сотрудник не найден'
            ], 'json');
            $response->setContent($content);
            return $response;
        }

        $employee
            ->setFullName($fullName)
            ->setDateOfEmployment(\DateTimeImmutable::createFromFormat('d.m.Y', $dateOfEmployment))
            ->setDepartment($department)
            ->setPosition($position)
            ->setStatus($status);

        if ($employee->getStatus() === 'уволен') {
            if ($dateOfDismissal) {
                $dateOfDismissal = DateTimeImmutable::createFromFormat('Y-m-d', $dateOfDismissal);
                $employee->setDateOfDismissal($dateOfDismissal);
            }

            if ($reasonForDismissal) {
                $employee->setReasonForDismissal($reasonForDismissal);
            }

            if ($categoryOfDismissal) {
                $employee->setCategoryOfDismissal($categoryOfDismissal);
            }
        }

        $employeeRepository->save($employee, true);

        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serializer->serialize(['message' => 'Сотрудник успешно изменен'], 'json'));
        return $response;
    }

    /**
     * @throws JsonException
     */
    #[Route('/employees/delete', name: 'app_employees_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EmployeeRepository $employeeRepository
    ): JsonResponse
    {
        $dataForDelete = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        foreach ($dataForDelete as $id) {
            $employeeRepository->remove($employeeRepository->find($id), true);
        }
        // отдаем ответ
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serializer->serialize([
            'position' => 'bottom-right',
            'message' => 'Сотрудники успешно удалены',
            'color' => 'success',
        ], 'json'));
        return $response;
    }

    private function validEmployee($fullName, $dateOfEmployment, $department, $position, $status): array
    {
        $content = [];

        if (!$fullName) {
            $content[] = ['message' => 'Не указано ФИО'];
        }

        if (!$dateOfEmployment) {
            $content[] = ['message' => 'Не указана дата трудоустройства'];
        }

        if (!$department) {
            $content[] = ['message' => 'Не указан отдел'];
        }

        if (!$position) {
            $content[] = ['message' => 'Не указана должность'];
        }

        if (!$status) {
            $content[] = ['message' => 'Не указан статус'];
        }

        return $content;
    }
}

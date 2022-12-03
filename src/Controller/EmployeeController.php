<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use App\Repository\TableRepository;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class EmployeeController extends AbstractController
{
    private Serializer $serializer;

    public function __construct()
    {
        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * @throws \JsonException
     */
    #[Route('/employees', name: 'app_employees', methods: ['GET'])]
    public function employees(
        Request $request,
        TableRepository $tableRepository,
        PaginatorInterface $paginator
    ): JsonResponse {
        // номер страницы
        $page = $request->query->get('page') ?: 1;

        // кол-во записей на странице
        $perPage = $request->query->get('perPage') ?: 10;

        // фильтр
        $filter = $request->query->get('filter') ? json_decode(
            $request->query->get('filter'),
            true,
            512,
            JSON_THROW_ON_ERROR
        ) : null;

        $sort = $request->query->get('sort') ? json_decode(
            $request->query->get('sort'),
            true,
            512,
            JSON_THROW_ON_ERROR
        ) : null;

        // записи до пагинации
        $tableData = $tableRepository->getTableData($filter, $sort);

        if ('Все' !== $perPage) {
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
        TableRepository $tableRepository
    ): JsonResponse {
        $employee = $tableRepository->findEmployeeInArray($id);

        $response = new JsonResponse();

        if (!$employee) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $this->serializer->serialize([
                'message' => 'Сотрудник не найден',
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
     * @throws \JsonException
     */
    #[Route('/employees/new', name: 'app_employees_new', methods: ['POST'])]
    public function new(
        Request $request,
        EmployeeRepository $employeeRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $employee = new Employee();
        $employee->fromJson($data);

        $response = new JsonResponse();

        $content = $this->validate($employee);
        if (count($content) > 0) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent($this->serializer->serialize(
                $content,
                'json'
            ));

            return $response;
        }
        $employeeRepository->save($employee, true);
        $response->setStatusCode(Response::HTTP_CREATED);
        $response->setContent($this->serializer->serialize(['message' => 'Сотрудник успешно создан'], 'json'));

        return $response;
    }

    /**
     * @throws \JsonException
     */
    #[Route('/employees/{id}', name: 'app_employees_put', methods: ['PUT'])]
    public function edit(
        int $id,
        Request $request,
        EmployeeRepository $employeeRepository
    ): JsonResponse {
        $response = new JsonResponse();

        $employee = $employeeRepository->find($id);
        if (!$employee) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $this->serializer->serialize([
                'message' => 'Сотрудник не найден',
            ], 'json');
            $response->setContent($content);

            return $response;
        }

        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $employee->fromJson($data);
        $content = $this->validate($employee);
        if (count($content) > 0) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent($this->serializer->serialize(
                $content,
                'json'
            ));

            return $response;
        }
        $employeeRepository->save($employee, true);
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serializer->serialize(['message' => 'Сотрудник успешно изменен'], 'json'));

        return $response;
    }

    /**
     * @throws \JsonException
     */
    #[Route('/employees/delete', name: 'app_employees_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EmployeeRepository $employeeRepository
    ): JsonResponse {
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

    private function validate(Employee $employee): array
    {
        $content = [];

        if (!$employee->getFullName()) {
            $content[] = ['message' => 'Не указано ФИО'];
        }

        if (!$employee->getDateOfEmployment()) {
            $content[] = ['message' => 'Не указана дата трудоустройства'];
        }

        if (!$employee->getDepartment()) {
            $content[] = ['message' => 'Не указан отдел'];
        }

        if (!$employee->getPosition()) {
            $content[] = ['message' => 'Не указана должность'];
        }

        if (!$employee->getStatus()) {
            $content[] = ['message' => 'Не указан статус'];
        }

        return $content;
    }
}

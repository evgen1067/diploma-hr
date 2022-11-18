<?php

namespace App\Controller;

use App\Repository\EmployeeRepository;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use JsonException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class ApiController extends AbstractController
{
    private Serializer $serializer;


    public function __construct() {
        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * @throws JsonException
     */
    #[Route('/employees', name: 'app_api', methods: ['GET'])]
    public function index(
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
}

<?php

namespace App\Controller;

use App\Repository\EmployeeRepository;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class ChartController extends AbstractController
{
    private Serializer $serializer;

    public function __construct() {
        $this->serializer = SerializerBuilder::create()->build();
    }

    #[Route('/layoffs', name: 'app_layoffs_chart', methods: ['GET'])]
    public function layoffs(
        Request $request,
        EmployeeRepository $employeeRepository
    ): JsonResponse
    {
        $department = $request->query->get('department');
        $range = $request->query->get('range');
        $work = $request->query->get('work');

        $result = $employeeRepository->findDataForLayoffsChart($department, $range, $work);
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serializer->serialize($result, 'json'));
        return $response;
    }
}

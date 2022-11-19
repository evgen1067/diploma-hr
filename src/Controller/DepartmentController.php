<?php

namespace App\Controller;

use App\Repository\EmployeeRepository;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class DepartmentController extends AbstractController
{
    private Serializer $serializer;

    public function __construct() {
        $this->serializer = SerializerBuilder::create()->build();
    }

    #[Route('/departments', name: 'app_departments', methods: ['GET'])]
    public function index(
        EmployeeRepository $employeeRepository
    ): Response
    {
        $result = $employeeRepository->findAllDepartments();
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serializer->serialize($result, 'json'));
        return $response;
    }
}

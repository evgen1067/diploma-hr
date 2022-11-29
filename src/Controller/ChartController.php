<?php

namespace App\Controller;

use App\Repository\EmployeeRepository;
use App\Repository\LayoffsRepository;
use App\Repository\TurnoverRepository;
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

    public function __construct()
    {
        $this->serializer = SerializerBuilder::create()->build();
    }

    #[Route('/chart-turnover', name: 'app_turnover_chart', methods: ['GET'])]
    public function turnover(
        Request $request,
        TurnoverRepository $turnoverRepository
    ): JsonResponse {
        $valueTo = $request->query->get('valueTo');
        if ($valueTo) {
            $valueTo = \DateTimeImmutable::createFromFormat('d.m.Y', $valueTo);
        } else {
            $valueTo = new \DateTimeImmutable();
        }

        $valueFrom = $request->query->get('valueFrom');
        $valueFrom = \DateTimeImmutable::createFromFormat('d.m.Y', $valueFrom);

        $department = $request->query->get('department');

        $result = $turnoverRepository->findDataForTurnoverRates($valueTo, $valueFrom, $department);
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serializer->serialize($result, 'json'));

        return $response;
    }

    #[Route('/chart-layoffs', name: 'app_layoffs_chart', methods: ['GET'])]
    public function chart(
        Request $request,
        LayoffsRepository $layoffsRepository
    ): JsonResponse {
        $valueTo = $request->query->get('valueTo');
        if ($valueTo) {
            $valueTo = \DateTimeImmutable::createFromFormat('d.m.Y', $valueTo);
        } else {
            $valueTo = new \DateTimeImmutable();
        }
        $valueFrom = $request->query->get('valueFrom');
        if ($valueFrom) {
            $valueFrom = \DateTimeImmutable::createFromFormat('d.m.Y', $valueFrom);
        } else {
            $valueFrom = new \DateTimeImmutable('-3 year');
        }

        $result = $layoffsRepository->findDataLayoffsChart($valueTo, $valueFrom);
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serializer->serialize($result, 'json'));

        return $response;
    }
}

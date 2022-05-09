<?php

namespace App\Controller;

use App\Controller\Response\ErrorResponse;
use App\Controller\Response\SuccessResponse;
use App\Integration\Shiptor\Api;
use App\Integration\Shiptor\Exception\ShiptorCalculateShippingException;
use App\Integration\Shiptor\Exception\ShiptorGetDeliveryCitiesException;
use App\Integration\Shiptor\Exception\ShiptorReadCitiesException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index', methods: 'GET')]
    public function index(Request $request, Api $shiptor): Response
    {
        return $this->render('index/index.html.twig');
    }

    #[Route('/calculate/shipping', name: 'app_calculate_shipping', methods: 'POST')]
    public function calculateShipping(Request $request, Api $shiptor): Response
    {
        $kladrId = $request->request->get('kladr_id');

        if (empty($kladrId)) {
            return (new ErrorResponse(404))
                ->addError(404, 'ID города не может быть пустым');
        }

        try {
            $shipping = $shiptor->calculateShipping($kladrId);
        } catch (ShiptorCalculateShippingException|ShiptorReadCitiesException|JsonException $e) {
            return (new ErrorResponse($e->getCode()))
                ->addError($e->getCode(), $e->getMessage());
        }

        return new SuccessResponse($shipping);
    }

    #[Route('/settlement/suggest', name: 'app_settlement_suggest', methods: 'POST')]
    public function suggestSettlement(Request $request, Api $shiptor): JsonResponse
    {
        $query = $request->request->get('query');

        if (empty($query)) {
            return (new ErrorResponse(404))
                ->addError(404, 'Запрос не может быть пустым');
        }

        try {
            $deliveryCities = $shiptor->suggestSettlement($query);
        } catch (ShiptorReadCitiesException|JsonException $e) {
            return (new ErrorResponse($e->getCode()))
                ->addError($e->getCode(), $e->getMessage());
        }

        return new SuccessResponse($deliveryCities);
    }

    #[Route('/deliver', name: 'app_deliver', methods: 'POST')]
    public function deliver(Request $request, Api $shiptor): JsonResponse
    {
        $data = $request->request->all();

        if (empty($data['kladr_id'])) {
            return (new ErrorResponse(404))
                ->addError(404, 'ID города не может быть пустым');
        }

        try {
            $deliveryCities = $shiptor->getDeliveryPoints($data['kladr_id']);
        } catch (ShiptorGetDeliveryCitiesException|ShiptorReadCitiesException|JsonException $e) {
            return (new ErrorResponse($e->getCode()))
                ->addError($e->getCode(), $e->getMessage());
        }

        return new SuccessResponse($deliveryCities);
    }
}

<?php

namespace App\Integration\Shiptor;

use App\Integration\Shiptor\Dto\CalculateShipping;
use App\Integration\Shiptor\Dto\DeliveryCitiesResponse;
use App\Integration\Shiptor\Dto\SettlementsResponse;
use App\Integration\Shiptor\Exception\ShiptorCalculateShippingException;
use App\Integration\Shiptor\Exception\ShiptorGetDeliveryCitiesException;
use App\Integration\Shiptor\Exception\ShiptorReadCitiesException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Api
{
    private $baseUrl = 'https://api.shiptor.ru/public/';

    public function __construct(private readonly ParameterBagInterface $parameterBag) {}

    /**
     * Расчет стоимости доставки
     *
     * @param $kladrId
     * @return CalculateShipping
     * @throws JsonException
     * @throws ShiptorCalculateShippingException
     * @throws ShiptorReadCitiesException
     */
    public function calculateShipping($kladrId): CalculateShipping
    {
        $authToken = $this->parameterBag->get('app.auth_token');

        $headers = [
            'Content-Type' => 'application/json',
            'x-authorization-token' => $authToken
        ];

        $client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $headers
        ]);

        $body = [
            "id" => "JsonRpcClient.js",
            "jsonrpc" => "2.0",
            "method" => "calculateShipping",
            "params" => [
                "kladr_id" => $kladrId,
                "length" => 20,
                "width" => 10,
                "height" => 20,
                "weight" => 1.5,
                "cod" => 0,
                "declared_cost" => 0
            ]
        ];

        try {
            $r = $client->post(
                'v1',
                [
                    'body' => json_encode($body, JSON_THROW_ON_ERROR)
                ]
            );
        } catch (GuzzleException) {
            throw new ShiptorCalculateShippingException('Ошибка при расчете стоимости доставки', 404);
        }

        try {
            $rawData = json_decode($r->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ShiptorReadCitiesException('Ошибка при чтении данных о населённых пунктах', 404);
        }

        return CalculateShipping::createFromArray($rawData);
    }

    /**
     * Получение списка ПВЗ (v2)
     *
     * @param string $id
     * @return DeliveryCitiesResponse
     * @throws JsonException
     * @throws ShiptorGetDeliveryCitiesException
     * @throws ShiptorReadCitiesException
     */
    public function getDeliveryPoints(string $id): DeliveryCitiesResponse
    {
        $authToken = $this->parameterBag->get('app.auth_token');

        $headers = [
            'Content-Type' => 'application/json',
            'x-authorization-token' => $authToken
        ];

        $client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $headers
        ]);

        $body = [
            "id" => "JsonRpcClient.js",
            "jsonrpc" => "2.0",
            "method" => "getDeliveryPoints",
            "params" => [
                "page" => 1,
                "per_page" => 10,
                "kladr_id" => $id,
                "limits" => [
                    "weight" => 1.5,
                    "length" => 20,
                    "width" => 10,
                    "height" => 20
                ]
            ]
        ];

        try {
            $r = $client->post(
                'v1',
                [
                    'body' => json_encode($body, JSON_THROW_ON_ERROR)
                ]
            );
        } catch (GuzzleException) {
            throw new ShiptorGetDeliveryCitiesException('Ошибка при получении способов доставки', 404);
        }

        try {
            $rawData = json_decode($r->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ShiptorReadCitiesException('Ошибка при чтении данных о населённых пунктах', 404);
        }

        return DeliveryCitiesResponse::createFromArray($rawData);
    }

    /**
     * Получение населенного пункта по части названия
     *
     * @param string $query
     * @return SettlementsResponse|null
     * @throws JsonException
     * @throws ShiptorReadCitiesException
     */
    public function suggestSettlement(string $query): ?SettlementsResponse
    {
        $authToken = $this->parameterBag->get('app.auth_token');

        $headers = [
            'Content-Type' => 'application/json',
            'x-authorization-token' => $authToken
        ];

        $client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $headers
        ]);

        $body = [
            "id" => "JsonRpcClient.js",
            "jsonrpc" => "2.0",
            "method" => "suggestSettlement",
            "params" => [
                "query" => $query
            ]
        ];

        try {
            $r = $client->post(
                'v1',
                [
                    'body' => json_encode($body, JSON_THROW_ON_ERROR)
                ]
            );
        } catch (GuzzleException) {
            return null;
        }

        try {
            $rawData = json_decode($r->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ShiptorReadCitiesException('Ошибка при чтении данных о населённых пунктах', 404);
        }

        return SettlementsResponse::createFromArray($rawData);
    }
}
<?php

namespace App\Integration\Shiptor\Dto;

/**
 * DTO для информации о пунктах выдачи заказов
 */
final class DeliveryCitiesResponse
{

    /**
     * @param array|null $deliveryCities
     */
    public function __construct(
        public ?array $deliveryCities = null,
    ) {}

    /**
     * Создание DTO
     *
     * @param array $data
     * @return static
     */
    public static function createFromArray(array $data): self
    {
        $response = new self();

        if (isset($data['result'])) {
            $response->deliveryCities = $data['result'];
        }

        return $response;
    }
}
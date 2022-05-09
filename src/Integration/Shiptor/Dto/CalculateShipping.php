<?php

namespace App\Integration\Shiptor\Dto;

/**
 * DTO для создания информации о расчёте стоимости доставки
 */
final class CalculateShipping
{

    /**
     * @param array|null $settlements
     */
    public function __construct(
        public ?array $settlements = null,
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
            $response->settlements = $data['result'];
        }

        return $response;
    }
}
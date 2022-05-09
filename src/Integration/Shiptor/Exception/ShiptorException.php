<?php

namespace App\Integration\Shiptor\Exception;

/**
 * @class ShiptorException
 * Класс для Исключений, сообщение которых не компрометируют систему,
 * и являются понятными для пользователя
 */
interface ShiptorException
{
    public function getMessage(): string;

    public function getCode();
}

<?php

namespace App\Controller\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Класс используемый для неудачного ответа
 */
final class ErrorResponse extends JsonResponse
{
    /**
     * @var array
     */
    private array $errors;

    /**
     * @param int $status
     * @param array $headers
     */
    public function __construct(int $status = 400, array $headers = [])
    {
        parent::__construct(
            $this->defaultResponse(),
            $status,
            $headers
        );
        $this->errors = [];
    }

    /**
     * Добавление ошибки
     *
     * @param string $code
     * @param string $message
     * @return $this
     */
    public function addError(string $code, string $message): self
    {
        $this->errors[] = [
            'code' => $code,
            'error' => $message,
        ];

        $this->setData([
            'errors' => $this->errors
        ]);

        return $this;
    }

    /**
     * Стандартный ответ
     *
     * @return string[][][]
     */
    private function defaultResponse(): array
    {
        return [
            'errors' => [
                [
                    'code' => '-1',
                    'error' => 'Oops, что-то пошло не так'
                ],
            ]
        ];
    }
}
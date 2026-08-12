<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi\Http;

final class Response
{
    public function __construct(
        public readonly int $statusCode,
        public readonly array $body,
    ) {
    }

    public static function json(array $body, int $statusCode = 200): self
    {
        return new self($statusCode, $body);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        header('Content-Type: application/json');

        echo json_encode($this->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
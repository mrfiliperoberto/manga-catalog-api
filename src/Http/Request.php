<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi\Http;

final class Request
{
    private function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $body,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = rtrim($path, '/');


    $rawBody = file_get_contents('php://input');
    $decodedBody = json_decode($rawBody, true);
    $body = is_array($decodedBody) ? $decodedBody : [];

    return new self(
        method: strtoupper($method),
        path: $path === '' ? '/' : $path,
        body: $body,
);
    }
}  
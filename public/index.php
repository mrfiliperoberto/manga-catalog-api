<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Mrfiliperoberto\MangaCatalogApi\Database\Connection;
use Mrfiliperoberto\MangaCatalogApi\Http\Request;
use Mrfiliperoberto\MangaCatalogApi\Http\Response;
use Mrfiliperoberto\MangaCatalogApi\Http\Router;
use Mrfiliperoberto\MangaCatalogApi\Manga;
use Mrfiliperoberto\MangaCatalogApi\SqliteMangaRepository;

$databasePath = __DIR__ . '/../database/catalog.sqlite';
$pdo = Connection::make($databasePath);
$repository = new SqliteMangaRepository($pdo);

$router = new Router();

$router->get('/manga', function (Request $request) use ($repository): Response {
    $mangas = array_map(
        fn (Manga $manga): array => $manga->toArray(),
        $repository->all(),
    );

    return Response::json($mangas);
});

$router->get('/manga/{id}', function (Request $request, array $parameters) use ($repository): Response {
    $manga = $repository->find((int) $parameters['id']);

    if ($manga === null) {
        return Response::json(['error' => 'Manga not found'], 404);
    }

    return Response::json($manga->toArray());
});

$router->post('/manga', function (Request $request) use ($repository): Response {
    $required = ['title', 'author', 'genre', 'status', 'volumes'];
    $missing = array_filter($required, fn (string $field): bool => !isset($request->body[$field]));

    if (!empty($missing)) {
        return Response::json([
            'error' => 'Validation failed',
            'missing_fields' => array_values($missing),
        ], 422);
    }

    if (!in_array($request->body['status'], ['ongoing', 'completed'], true)) {
        return Response::json([
            'error' => 'Validation failed',
            'message' => 'status must be either "ongoing" or "completed"',
        ], 422);
    }

    $manga = Manga::fromArray([
        ...$request->body,
        'created_at' => date(DATE_ATOM),
    ]);

    $created = $repository->create($manga);

    return Response::json($created->toArray(), 201);
});

$router->put('/manga/{id}', function (Request $request, array $parameters) use ($repository): Response {
    $existing = $repository->find((int) $parameters['id']);

    if ($existing === null) {
        return Response::json(['error' => 'Manga not found'], 404);
    }

    $updatedData = [...$existing->toArray(), ...$request->body];
    $manga = Manga::fromArray($updatedData);

    $updated = $repository->update((int) $parameters['id'], $manga);

    return Response::json($updated->toArray());
});

$router->delete('/manga/{id}', function (Request $request, array $parameters) use ($repository): Response {
    $deleted = $repository->delete((int) $parameters['id']);

    if (!$deleted) {
        return Response::json(['error' => 'Manga not found'], 404);
    }

    return Response::json(['message' => 'Manga deleted successfully']);
});

$request = Request::fromGlobals();
$response = $router->dispatch($request);
$response->send();
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Mrfiliperoberto\MangaCatalogApi\AuthService;
use Mrfiliperoberto\MangaCatalogApi\Database\Connection;
use Mrfiliperoberto\MangaCatalogApi\Http\Request;
use Mrfiliperoberto\MangaCatalogApi\Http\Response;
use Mrfiliperoberto\MangaCatalogApi\Http\Router;
use Mrfiliperoberto\MangaCatalogApi\Manga;
use Mrfiliperoberto\MangaCatalogApi\SqliteMangaRepository;
use Mrfiliperoberto\MangaCatalogApi\SqliteUserRepository;

session_start();

$databasePath = __DIR__ . '/../database/catalog.sqlite';
$pdo = Connection::make($databasePath);
$mangaRepository = new SqliteMangaRepository($pdo);
$userRepository = new SqliteUserRepository($pdo);
$authService = new AuthService($userRepository);

function requireAuth(): ?Response
{
    if (!isset($_SESSION['user_id'])) {
        return Response::json(['error' => 'Unauthorized. Please log in.'], 401);
    }

    return null;
}

$router = new Router();

// --- Auth routes ---

$router->post('/register', function (Request $request) use ($authService): Response {
    try {
        $user = $authService->register(
            $request->body['username'] ?? '',
            $request->body['password'] ?? '',
        );
    } catch (\InvalidArgumentException $exception) {
        return Response::json(['error' => $exception->getMessage()], 422);
    }

    return Response::json($user->toArray(), 201);
});

$router->post('/login', function (Request $request) use ($authService): Response {
    $user = $authService->attempt(
        $request->body['username'] ?? '',
        $request->body['password'] ?? '',
    );

    if ($user === null) {
        return Response::json(['error' => 'Invalid credentials'], 401);
    }

    $_SESSION['user_id'] = $user->id;

    return Response::json(['message' => 'Logged in successfully', 'user' => $user->toArray()]);
});

$router->post('/logout', function (Request $request): Response {
    session_destroy();

    return Response::json(['message' => 'Logged out successfully']);
});

// --- Manga routes ---

$router->get('/manga', function (Request $request) use ($mangaRepository): Response {
    $mangas = array_map(
        fn (Manga $manga): array => $manga->toArray(),
        $mangaRepository->all(),
    );

    return Response::json($mangas);
});

$router->get('/manga/{id}', function (Request $request, array $parameters) use ($mangaRepository): Response {
    $manga = $mangaRepository->find((int) $parameters['id']);

    if ($manga === null) {
        return Response::json(['error' => 'Manga not found'], 404);
    }

    return Response::json($manga->toArray());
});

$router->post('/manga', function (Request $request) use ($mangaRepository): Response {
    if ($authError = requireAuth()) {
        return $authError;
    }

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

    $created = $mangaRepository->create($manga);

    return Response::json($created->toArray(), 201);
});

$router->put('/manga/{id}', function (Request $request, array $parameters) use ($mangaRepository): Response {
    if ($authError = requireAuth()) {
        return $authError;
    }

    $existing = $mangaRepository->find((int) $parameters['id']);

    if ($existing === null) {
        return Response::json(['error' => 'Manga not found'], 404);
    }

    $updatedData = [...$existing->toArray(), ...$request->body];
    $manga = Manga::fromArray($updatedData);

    $updated = $mangaRepository->update((int) $parameters['id'], $manga);

    return Response::json($updated->toArray());
});

$router->delete('/manga/{id}', function (Request $request, array $parameters) use ($mangaRepository): Response {
    if ($authError = requireAuth()) {
        return $authError;
    }

    $deleted = $mangaRepository->delete((int) $parameters['id']);

    if (!$deleted) {
        return Response::json(['error' => 'Manga not found'], 404);
    }

    return Response::json(['message' => 'Manga deleted successfully']);
});

$request = Request::fromGlobals();
$response = $router->dispatch($request);
$response->send();
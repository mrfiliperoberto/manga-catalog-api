<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi\Tests\Http;

use Mrfiliperoberto\MangaCatalogApi\Http\Request;
use Mrfiliperoberto\MangaCatalogApi\Http\Response;
use Mrfiliperoberto\MangaCatalogApi\Http\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private function makeRequest(string $method, string $path): Request
    {
        $reflection = new \ReflectionClass(Request::class);
        $request = $reflection->newInstanceWithoutConstructor();

        $reflection->getProperty('method')->setValue($request, $method);
        $reflection->getProperty('path')->setValue($request, $path);
        $reflection->getProperty('body')->setValue($request, []);

        return $request;
    }

    public function testItDispatchesASimpleRoute(): void
    {
        $router = new Router();
        $router->get('/manga', fn (): Response => Response::json(['message' => 'list']));

        $response = $router->dispatch($this->makeRequest('GET', '/manga'));

        $this->assertSame(200, $response->statusCode);
        $this->assertSame(['message' => 'list'], $response->body);
    }

    public function testItExtractsRouteParameters(): void
    {
        $router = new Router();
        $router->get(
            '/manga/{id}',
            fn (Request $request, array $parameters): Response => Response::json(['id' => $parameters['id']]),
        );

        $response = $router->dispatch($this->makeRequest('GET', '/manga/5'));

        $this->assertSame(['id' => '5'], $response->body);
    }

    public function testItReturns404WhenRouteDoesNotExist(): void
    {
        $router = new Router();
        $router->get('/manga', fn (): Response => Response::json(['message' => 'list']));

        $response = $router->dispatch($this->makeRequest('GET', '/unknown'));

        $this->assertSame(404, $response->statusCode);
    }

    public function testItReturns404WhenMethodDoesNotMatch(): void
    {
        $router = new Router();
        $router->get('/manga', fn (): Response => Response::json(['message' => 'list']));

        $response = $router->dispatch($this->makeRequest('POST', '/manga'));

        $this->assertSame(404, $response->statusCode);
    }
}
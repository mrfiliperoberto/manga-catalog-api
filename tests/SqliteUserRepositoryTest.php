<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi\Tests;

use Mrfiliperoberto\MangaCatalogApi\SqliteUserRepository;
use Mrfiliperoberto\MangaCatalogApi\User;
use PDO;
use PHPUnit\Framework\TestCase;

final class SqliteUserRepositoryTest extends TestCase
{
    private PDO $pdo;
    private SqliteUserRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo->exec(<<<SQL
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                created_at TEXT NOT NULL
            )
        SQL);

        $this->repository = new SqliteUserRepository($this->pdo);
    }

    private function makeUser(string $username = 'lipao'): User
    {
        return new User(
            id: null,
            username: $username,
            passwordHash: password_hash('secret123', PASSWORD_DEFAULT),
            createdAt: '2026-01-01T10:00:00+00:00',
        );
    }

    public function testItCreatesAndReturnsAUserWithGeneratedId(): void
    {
        $created = $this->repository->create($this->makeUser());

        $this->assertNotNull($created->id);
        $this->assertSame('lipao', $created->username);
    }

    public function testItFindsAUserByUsername(): void
    {
        $this->repository->create($this->makeUser());

        $found = $this->repository->findByUsername('lipao');

        $this->assertNotNull($found);
        $this->assertSame('lipao', $found->username);
    }

    public function testItReturnsNullWhenUsernameNotFound(): void
    {
        $this->assertNull($this->repository->findByUsername('nobody'));
    }
}
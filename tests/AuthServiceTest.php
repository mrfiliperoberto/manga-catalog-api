<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi\Tests;

use Mrfiliperoberto\MangaCatalogApi\AuthService;
use Mrfiliperoberto\MangaCatalogApi\SqliteUserRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    private PDO $pdo;
    private AuthService $authService;

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

        $repository = new SqliteUserRepository($this->pdo);
        $this->authService = new AuthService($repository);
    }

    public function testItRegistersANewUser(): void
    {
        $user = $this->authService->register('lipao', 'senha12345');

        $this->assertNotNull($user->id);
        $this->assertSame('lipao', $user->username);
    }

    public function testRegisteredPasswordIsHashedNotPlainText(): void
    {
        $user = $this->authService->register('lipao', 'senha12345');

        $this->assertNotSame('senha12345', $user->passwordHash);
        $this->assertTrue(password_verify('senha12345', $user->passwordHash));
    }

    public function testItRejectsRegistrationWithEmptyUsername(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->authService->register('', 'senha12345');
    }

    public function testItRejectsRegistrationWithShortPassword(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->authService->register('lipao', '123');
    }

    public function testItRejectsRegistrationWithDuplicateUsername(): void
    {
        $this->authService->register('lipao', 'senha12345');

        $this->expectException(\InvalidArgumentException::class);

        $this->authService->register('lipao', 'outrasenha123');
    }

    public function testItAuthenticatesWithCorrectCredentials(): void
    {
        $this->authService->register('lipao', 'senha12345');

        $user = $this->authService->attempt('lipao', 'senha12345');

        $this->assertNotNull($user);
        $this->assertSame('lipao', $user->username);
    }

    public function testItRejectsAuthenticationWithWrongPassword(): void
    {
        $this->authService->register('lipao', 'senha12345');

        $user = $this->authService->attempt('lipao', 'senhaerrada');

        $this->assertNull($user);
    }

    public function testItRejectsAuthenticationForNonexistentUser(): void
    {
        $user = $this->authService->attempt('nobody', 'senha12345');

        $this->assertNull($user);
    }
}
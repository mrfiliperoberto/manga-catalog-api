<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi;

use PDO;

final class SqliteUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function findByUsername(string $username): ?User
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $statement->execute(['username' => $username]);

        $row = $statement->fetch();

        return $row === false ? null : User::fromArray($row);
    }

    public function create(User $user): User
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash, created_at)
             VALUES (:username, :password_hash, :created_at)',
        );

        $statement->execute([
            'username' => $user->username,
            'password_hash' => $user->passwordHash,
            'created_at' => $user->createdAt,
        ]);

        $newId = (int) $this->pdo->lastInsertId();

        return new User(
            id: $newId,
            username: $user->username,
            passwordHash: $user->passwordHash,
            createdAt: $user->createdAt,
        );
    }
}
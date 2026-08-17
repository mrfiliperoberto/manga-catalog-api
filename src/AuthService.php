<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi;

final class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function register(string $username, string $password): User
    {
        $username = trim($username);

        if ($username === '' || $password === '') {
            throw new \InvalidArgumentException('username and password are required');
        }

        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('password must be at least 8 characters long');
        }

        if ($this->repository->findByUsername($username) !== null) {
            throw new \InvalidArgumentException('username is already taken');
        }

        $user = new User(
            id: null,
            username: $username,
            passwordHash: password_hash($password, PASSWORD_DEFAULT),
            createdAt: date(DATE_ATOM),
        );

        return $this->repository->create($user);
    }

    public function attempt(string $username, string $password): ?User
    {
        $user = $this->repository->findByUsername($username);

        if ($user === null || !password_verify($password, $user->passwordHash)) {
            return null;
        }

        return $user;
    }
}
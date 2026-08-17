<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?User;

    public function create(User $user): User;
}
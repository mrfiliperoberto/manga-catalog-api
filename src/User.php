<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi;

final class User
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $username,
        public readonly string $passwordHash,
        public readonly string $createdAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'created_at' => $this->createdAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            username: $data['username'],
            passwordHash: $data['password_hash'],
            createdAt: $data['created_at'],
        );
    }
}
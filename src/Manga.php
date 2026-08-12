<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi;

final class Manga
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly string $author,
        public readonly string $genre,
        public readonly string $status,
        public readonly int $volumes,
        public readonly string $createdAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'genre' => $this->genre,
            'status' => $this->status,
            'volumes' => $this->volumes,
            'created_at' => $this->createdAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            title: $data['title'],
            author: $data['author'],
            genre: $data['genre'],
            status: $data['status'],
            volumes: (int) $data['volumes'],
            createdAt: $data['created_at'],
        );
    }
}
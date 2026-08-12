<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi;

use PDO;

final class SqliteMangaRepository implements MangaRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM manga ORDER BY id ASC');

        $rows = $statement->fetchAll();

        return array_map(
            fn (array $row): Manga => Manga::fromArray($row),
            $rows,
        );
    }

    public function find(int $id): ?Manga
    {
        $statement = $this->pdo->prepare('SELECT * FROM manga WHERE id = :id');
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        return $row === false ? null : Manga::fromArray($row);
    }

    public function create(Manga $manga): Manga
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO manga (title, author, genre, status, volumes, created_at)
             VALUES (:title, :author, :genre, :status, :volumes, :created_at)',
        );

        $statement->execute([
            'title' => $manga->title,
            'author' => $manga->author,
            'genre' => $manga->genre,
            'status' => $manga->status,
            'volumes' => $manga->volumes,
            'created_at' => $manga->createdAt,
        ]);

        $newId = (int) $this->pdo->lastInsertId();

        return $this->find($newId);
    }

    public function update(int $id, Manga $manga): ?Manga
    {
        if ($this->find($id) === null) {
            return null;
        }

        $statement = $this->pdo->prepare(
            'UPDATE manga
             SET title = :title, author = :author, genre = :genre,
                 status = :status, volumes = :volumes
             WHERE id = :id',
        );

        $statement->execute([
            'title' => $manga->title,
            'author' => $manga->author,
            'genre' => $manga->genre,
            'status' => $manga->status,
            'volumes' => $manga->volumes,
            'id' => $id,
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        if ($this->find($id) === null) {
            return false;
        }

        $statement = $this->pdo->prepare('DELETE FROM manga WHERE id = :id');
        $statement->execute(['id' => $id]);

        return true;
    }
}
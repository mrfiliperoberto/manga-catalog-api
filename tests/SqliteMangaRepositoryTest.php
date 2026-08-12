<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi\Tests;

use Mrfiliperoberto\MangaCatalogApi\Manga;
use Mrfiliperoberto\MangaCatalogApi\SqliteMangaRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class SqliteMangaRepositoryTest extends TestCase
{
    private PDO $pdo;
    private SqliteMangaRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo->exec(<<<SQL
            CREATE TABLE manga (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                author TEXT NOT NULL,
                genre TEXT NOT NULL,
                status TEXT NOT NULL,
                volumes INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
            )
        SQL);

        $this->repository = new SqliteMangaRepository($this->pdo);
    }

    private function makeManga(): Manga
    {
        return new Manga(
            id: null,
            title: 'Berserk',
            author: 'Kentaro Miura',
            genre: 'Dark Fantasy',
            status: 'ongoing',
            volumes: 41,
            createdAt: '2026-01-01T10:00:00+00:00',
        );
    }

    public function testItReturnsEmptyArrayWhenThereAreNoRecords(): void
    {
        $this->assertSame([], $this->repository->all());
    }

    public function testItCreatesAndReturnsAMangaWithGeneratedId(): void
    {
        $created = $this->repository->create($this->makeManga());

        $this->assertNotNull($created->id);
        $this->assertSame('Berserk', $created->title);
    }

    public function testItFindsAMangaById(): void
    {
        $created = $this->repository->create($this->makeManga());

        $found = $this->repository->find($created->id);

        $this->assertNotNull($found);
        $this->assertSame('Berserk', $found->title);
    }

    public function testItReturnsNullWhenMangaNotFound(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function testItListsAllMangas(): void
    {
        $this->repository->create($this->makeManga());
        $this->repository->create($this->makeManga());

        $this->assertCount(2, $this->repository->all());
    }

    public function testItUpdatesAnExistingManga(): void
    {
        $created = $this->repository->create($this->makeManga());

        $updated = new Manga(
            id: $created->id,
            title: 'Berserk',
            author: 'Kentaro Miura',
            genre: 'Dark Fantasy',
            status: 'completed',
            volumes: 41,
            createdAt: $created->createdAt,
        );

        $result = $this->repository->update($created->id, $updated);

        $this->assertSame('completed', $result->status);
    }

    public function testUpdateReturnsNullWhenMangaDoesNotExist(): void
    {
        $result = $this->repository->update(999, $this->makeManga());

        $this->assertNull($result);
    }

    public function testItDeletesAnExistingManga(): void
    {
        $created = $this->repository->create($this->makeManga());

        $result = $this->repository->delete($created->id);

        $this->assertTrue($result);
        $this->assertNull($this->repository->find($created->id));
    }

    public function testDeleteReturnsFalseWhenMangaDoesNotExist(): void
    {
        $this->assertFalse($this->repository->delete(999));
    }
}
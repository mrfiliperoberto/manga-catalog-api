<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi\Tests;

use Mrfiliperoberto\MangaCatalogApi\Manga;
use PHPUnit\Framework\TestCase;

final class MangaTest extends TestCase
{
    public function testItCreatesAMangaWithAllFields(): void
    {
        $manga = new Manga(
            id: 1,
            title: 'Berserk',
            author: 'Kentaro Miura',
            genre: 'Dark Fantasy',
            status: 'ongoing',
            volumes: 41,
            createdAt: '2026-01-01T10:00:00+00:00',
        );

        $this->assertSame(1, $manga->id);
        $this->assertSame('Berserk', $manga->title);
        $this->assertSame(41, $manga->volumes);
    }

    public function testItConvertsToArray(): void
    {
        $manga = new Manga(
            id: 1,
            title: 'Berserk',
            author: 'Kentaro Miura',
            genre: 'Dark Fantasy',
            status: 'ongoing',
            volumes: 41,
            createdAt: '2026-01-01T10:00:00+00:00',
        );

        $this->assertSame([
            'id' => 1,
            'title' => 'Berserk',
            'author' => 'Kentaro Miura',
            'genre' => 'Dark Fantasy',
            'status' => 'ongoing',
            'volumes' => 41,
            'created_at' => '2026-01-01T10:00:00+00:00',
        ], $manga->toArray());
    }

    public function testItRebuildsFromArrayWithoutId(): void
    {
        $data = [
            'title' => 'One Piece',
            'author' => 'Eiichiro Oda',
            'genre' => 'Shonen',
            'status' => 'ongoing',
            'volumes' => '110',
            'created_at' => '2026-01-01T10:00:00+00:00',
        ];

        $manga = Manga::fromArray($data);

        $this->assertNull($manga->id);
        $this->assertSame('One Piece', $manga->title);
        $this->assertSame(110, $manga->volumes);
    }
}
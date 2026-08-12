<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi;

interface MangaRepositoryInterface
{
    /**
     * @return Manga[]
     */
    public function all(): array;

    public function find(int $id): ?Manga;

    public function create(Manga $manga): Manga;

    public function update(int $id, Manga $manga): ?Manga;

    public function delete(int $id): bool;
}
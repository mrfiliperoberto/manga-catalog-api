# Manga Catalog API

A REST API for managing a manga catalog, built in plain PHP (no framework) as a learning project to practice HTTP routing, PDO with prepared statements, and clean architecture.

## Features

- Full CRUD for manga entries (Create, Read, Update, Delete)
- Data persisted in SQLite via PDO with prepared statements (SQL injection safe)
- Custom lightweight router with support for route parameters
- Input validation with proper HTTP status codes
- Fully covered by unit tests (PHPUnit)

## Requirements

- PHP >= 8.1 (with `pdo_sqlite` extension enabled)
- Composer

## Installation

```bash
git clone https://github.com/mrfiliperoberto/manga-catalog-api.git
cd manga-catalog-api
composer install
php database/migrate.php
```

## Running the server

```bash
php -S localhost:8000 -t public
```

## API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/manga` | List all manga |
| GET | `/manga/{id}` | Get a single manga |
| POST | `/manga` | Create a new manga |
| PUT | `/manga/{id}` | Update an existing manga (partial update supported) |
| DELETE | `/manga/{id}` | Delete a manga |

### Example requests

**Create a manga**

```bash
curl -X POST http://localhost:8000/manga \
  -H "Content-Type: application/json" \
  -d '{"title": "Berserk", "author": "Kentaro Miura", "genre": "Dark Fantasy", "status": "ongoing", "volumes": 41}'
```

Response (`201 Created`):

```json
{
    "id": 1,
    "title": "Berserk",
    "author": "Kentaro Miura",
    "genre": "Dark Fantasy",
    "status": "ongoing",
    "volumes": 41,
    "created_at": "2026-08-12T23:36:11+00:00"
}
```

**List all manga**

```bash
curl http://localhost:8000/manga
```

**Update a manga (partial)**

```bash
curl -X PUT http://localhost:8000/manga/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'
```

**Delete a manga**

```bash
curl -X DELETE http://localhost:8000/manga/1
```

### Validation errors

Creating a manga without required fields returns `422 Unprocessable Entity`:

```json
{
    "error": "Validation failed",
    "missing_fields": ["author", "genre"]
}
```

## Running tests

```bash
vendor/bin/phpunit tests
```

## Architecture

```
src/
├── Manga.php                        # Entity representing a single manga
├── MangaRepositoryInterface.php      # Contract for manga persistence
├── SqliteMangaRepository.php         # SQLite implementation via PDO
├── Database/
│   └── Connection.php                # PDO connection singleton
└── Http/
    ├── Request.php                   # Wraps PHP superglobals into a clean object
    ├── Response.php                  # Standardized JSON response
    └── Router.php                    # Maps HTTP method + path to a handler

public/
└── index.php                         # Entry point: registers routes and dispatches requests

database/
└── migrate.php                       # Creates the SQLite schema
```

Key design decisions:

- **Prepared statements everywhere**: every query with dynamic input uses named placeholders via PDO, preventing SQL injection by design.
- **Repository pattern**: route handlers depend on `MangaRepositoryInterface`, not on the SQLite implementation directly — a future MySQL or PostgreSQL repository would require no changes to routing logic.
- **Immutable entity**: `Manga` has no setters; updates are handled by creating a new instance and persisting it through the repository.
- **Named constructor for `Request`**: the constructor is private, and `Request::fromGlobals()` is the only way to build one from real PHP superglobals — this keeps construction explicit and testable via PHP's Reflection API.

## License

This project is licensed under the MIT License.
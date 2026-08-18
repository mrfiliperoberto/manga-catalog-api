# Manga Catalog API

A REST API for managing a manga catalog, built in plain PHP (no framework) as a learning project to practice HTTP routing, PDO with prepared statements, session-based authentication, and clean architecture.

## Features

- Full CRUD for manga entries (Create, Read, Update, Delete)
- User registration and login with hashed passwords and session-based authentication
- Write operations (`POST`, `PUT`, `DELETE` on `/manga`) require authentication; reads remain public
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

### Authentication

| Method | Endpoint | Description | Auth required |
|---|---|---|---|
| POST | `/register` | Create a new user account | No |
| POST | `/login` | Authenticate and start a session | No |
| POST | `/logout` | End the current session | No |

### Manga

| Method | Endpoint | Description | Auth required |
|---|---|---|---|
| GET | `/manga` | List all manga | No |
| GET | `/manga/{id}` | Get a single manga | No |
| POST | `/manga` | Create a new manga | **Yes** |
| PUT | `/manga/{id}` | Update an existing manga (partial update supported) | **Yes** |
| DELETE | `/manga/{id}` | Delete a manga | **Yes** |

### Authentication examples

**Register a new user**

```bash
curl -X POST http://localhost:8000/register \
  -H "Content-Type: application/json" \
  -d '{"username": "lipao", "password": "senha12345"}' \
  -c cookies.txt
```

**Log in**

```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{"username": "lipao", "password": "senha12345"}' \
  -c cookies.txt -b cookies.txt
```

**Create a manga (authenticated)**

```bash
curl -X POST http://localhost:8000/manga \
  -H "Content-Type: application/json" \
  -d '{"title": "Berserk", "author": "Kentaro Miura", "genre": "Dark Fantasy", "status": "ongoing", "volumes": 41}' \
  -b cookies.txt
```

Attempting a write operation without an active session returns `401 Unauthorized`:

```json
{
    "error": "Unauthorized. Please log in."
}
```

> `-c cookies.txt` saves the session cookie received from the server; `-b cookies.txt` sends it back on subsequent requests, simulating how a browser keeps a session alive between calls.

### Manga examples

**List all manga**

```bash
curl http://localhost:8000/manga
```

**Update a manga (partial)**

```bash
curl -X PUT http://localhost:8000/manga/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}' \
  -b cookies.txt
```

**Delete a manga**

```bash
curl -X DELETE http://localhost:8000/manga/1 -b cookies.txt
```

### Validation errors

Creating a manga without required fields returns `422 Unprocessable Entity`:

```json
{
    "error": "Validation failed",
    "missing_fields": ["author", "genre"]
}
```

Registering with a username that's already taken also returns `422`:

```json
{
    "error": "username is already taken"
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
├── User.php                          # Entity representing a single user
├── UserRepositoryInterface.php       # Contract for user persistence
├── SqliteUserRepository.php          # SQLite implementation via PDO
├── AuthService.php                   # Registration and login business logic
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
- **Repository pattern**: route handlers depend on `MangaRepositoryInterface` and `UserRepositoryInterface`, not on the SQLite implementations directly — a future MySQL or PostgreSQL repository would require no changes to routing or business logic.
- **Immutable entities**: `Manga` and `User` have no setters; updates are handled by creating a new instance and persisting it through the repository.
- **Named constructor for `Request`**: the constructor is private, and `Request::fromGlobals()` is the only way to build one from real PHP superglobals — this keeps construction explicit and testable via PHP's Reflection API.
- **Passwords are never stored or exposed in plain text**: `password_hash()`/`password_verify()` handle hashing and verification; `User::toArray()` deliberately excludes the password hash from any API response.
- **Business logic separated from HTTP concerns**: `AuthService` handles registration and authentication rules independently of sessions or requests, making it fully testable without spinning up a server.
- **Public reads, protected writes**: a small `requireAuth()` guard checks for an active session before write operations on `/manga`, while `GET` endpoints remain open — a common real-world API pattern.

## License

This project is licensed under the MIT License.
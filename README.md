# Todo PHP API (Industry-Standard Structure)

Production-style REST API for XAMPP: layered architecture, environment config, secure tokens, and API versioning.

## Architecture

```
todo/
├── public/index.php          # Front controller (only web-exposed entry)
├── routes/api.php            # Route definitions
├── src/App/
│   ├── Controllers/          # HTTP layer (thin)
│   ├── Services/             # Business logic
│   ├── Repositories/         # Database access (PDO)
│   ├── Middleware/           # CORS, authentication
│   ├── Http/                 # Request, Response, Router
│   ├── Exceptions/           # Typed HTTP errors
│   └── Infrastructure/       # Database connection
├── .env                      # Local secrets (not in git)
├── composer.json             # PSR-4 autoloading
└── database/schema.sql
```

## Practices used

| Practice | Implementation |
|----------|----------------|
| Front controller | `public/index.php` |
| PSR-4 autoloading | Composer `App\` namespace |
| Environment config | `.env` via `vlucas/phpdotenv` |
| Layered design | Controller → Service → Repository |
| API versioning | `/api/v1/...` (+ legacy `/api/...`) |
| Secure auth tokens | Only **hashed** tokens stored in DB (SHA-256) |
| Password security | `password_hash()` / `password_verify()`, min 8 chars |
| Prepared statements | All queries use PDO bindings |
| Error handling | Global handler; hide details when `APP_DEBUG=false` |
| CORS & security headers | Configurable origins, `X-Frame-Options`, etc. |
| Separation of concerns | No SQL in controllers |

## Run with Docker (easiest for others)

```bash
docker compose up --build
```

Open **http://localhost:8888** — see **[DOCKER.md](DOCKER.md)**.

## Setup (XAMPP / local)

1. Start **Apache** and **MySQL** in XAMPP.
2. Copy environment file:
   ```
   copy .env.example .env
   ```
3. Edit `.env` if MySQL password is not empty.
4. Install dependencies:
   ```
   C:\xampp\php\php.exe C:\xampp\php\composer.phar install
   ```
5. Create database `todo` in phpMyAdmin.
6. Run setup: **http://localhost/todo/install.php** then delete `install.php`.

## API base URL

- Versioned (recommended): `http://localhost/todo/api/v1`
- Legacy compatible: `http://localhost/todo/api`

## Endpoints

| Method | Path | Auth |
|--------|------|------|
| GET | `/api/v1` | No |
| POST | `/api/v1/auth/register` | No |
| POST | `/api/v1/auth/login` | No |
| POST | `/api/v1/auth/logout` | Bearer |
| GET | `/api/v1/auth/me` | Bearer |
| GET | `/api/v1/todos` | Bearer |
| POST | `/api/v1/todos` | Bearer |
| GET | `/api/v1/todos/{id}` | Bearer |
| PUT | `/api/v1/todos/{id}` | Bearer |
| PATCH | `/api/v1/todos/{id}/toggle` | Bearer |
| DELETE | `/api/v1/todos/{id}` | Bearer |

## Example: Register & create todo

```bash
curl -X POST http://localhost/todo/api/v1/auth/register ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"John\",\"email\":\"john@test.com\",\"password\":\"secret123\"}"
```

Use the returned `token`:

```bash
curl -X POST http://localhost/todo/api/v1/todos ^
  -H "Content-Type: application/json" ^
  -H "Authorization: Bearer YOUR_TOKEN" ^
  -d "{\"title\":\"Buy milk\"}"
```

## Production checklist

- Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
- Set `CORS_ALLOWED_ORIGINS` to your frontend URL (not `*`)
- Delete `install.php`
- Ensure `src/` and `.env` are not publicly accessible (only `public/` is served)

## Note after upgrade

Auth tokens are now stored as **hashes** in the database. Users who registered before this upgrade must **log in again** to receive a new token.

## React frontend

```bash
cd frontend
npm install
npm start
```

Open **http://localhost:5173** — see [frontend/README.md](frontend/README.md).

## Backend tests (local)

```bash
composer test
```

See **[docs/TESTING.md](docs/TESTING.md)** for mocks vs integration tests.

## CI (GitHub Actions)

On every push or PR to **`staging`** or **`main`**, [`.github/workflows/ci.yml`](.github/workflows/ci.yml) runs:

| Job | What it does |
|-----|----------------|
| **Backend** | `composer install`, `composer test`, Docker build (`backend/Dockerfile`) |
| **Frontend** | `npm ci`, `npm run lint`, `npm run build`, Docker build (`frontend/Dockerfile`) |

No deployment to VMs — tests and builds only.

### Branch flow

```
feature branch → PR → staging  (CI must pass)
staging        → PR → main     (CI must pass)
```

Optional: in GitHub **Settings → Branches**, add rules on `staging` and `main` requiring the **CI** check to pass before merge.

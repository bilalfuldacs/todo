# Run with Docker

One command starts **MySQL + PHP API + React frontend**.

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (includes Docker Compose)

## Start

From the project root:

```bash
docker compose up --build
```

Wait until you see the containers running, then open:

**http://localhost:8888**

Register a user and use the todo app.

## Stop

```bash
docker compose down
```

Remove database data too:

```bash
docker compose down -v
```

## What runs

| Service | Role | Port |
|---------|------|------|
| `db` | MySQL 8 + auto schema | internal |
| `api` | PHP backend (`backend/Dockerfile`) | internal |
| `web` | React app (`frontend/Dockerfile`) | **8888** |

The frontend proxies `/api` to the backend, so the browser uses one URL.

## Files to share

Give others the whole repo (or zip). They only need:

- `docker-compose.yml`
- `backend/Dockerfile` (+ `apache.conf`, `docker-entrypoint.sh`)
- `frontend/Dockerfile` (+ `nginx.conf`)
- Application source code

## Troubleshooting

| Issue | Fix |
|-------|-----|
| Port in use | Change `8888:80` to another host port (e.g. `3000:80`) in `docker-compose.yml` |
| DB connection error | Wait for `db` healthcheck; run `docker compose down -v` and up again |
| Old data | `docker compose down -v` then `docker compose up --build` |

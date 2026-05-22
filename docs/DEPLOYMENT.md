# Deployment Guide (GCP + Docker + GitHub Actions)

Deploy the Todo app to **staging** and **production** with separate GCP Cloud SQL databases, Docker images in **Artifact Registry**, and **GitHub Actions**.

## Architecture

```
Internet → VM (staging/prod)
            └── web (nginx) :80  →  static React + proxy /api → api (PHP Apache) :80
                                          ↓
                                   GCP Cloud SQL (MySQL)
```

- **staging** branch → staging server + staging database  
- **main** branch → production server + production database  

## 1. GCP setup

### Cloud SQL (two instances)

Create two MySQL instances (or databases):

| Environment | Example database | Example user |
|-------------|------------------|--------------|
| Staging | `todo_staging` | `todo_staging_user` |
| Production | `todo_prod` | `todo_prod_user` |

Import schema on each:

```bash
mysql -h STAGING_IP -u user -p todo_staging < database/schema.sql
mysql -h PROD_IP -u user -p todo_prod < database/schema.sql
```

Allow your VM IPs (or use [Cloud SQL Auth Proxy](https://cloud.google.com/sql/docs/mysql/connect-auth-proxy)).

### Artifact Registry

```bash
gcloud artifacts repositories create todo \
  --repository-format=docker \
  --location=us-central1 \
  --description="Todo app images"
```

### Service account (for GitHub Actions)

1. Create SA: `github-actions-deploy`
2. Roles:
   - `Artifact Registry Writer`
   - (Optional) `Cloud SQL Client` if using proxy on VM
3. Create JSON key → GitHub secret `GCP_SA_KEY`

## 2. GitHub configuration

### Repository secrets

| Secret | Description |
|--------|-------------|
| `GCP_SA_KEY` | Service account JSON key |
| `GCP_PROJECT_ID` | GCP project ID |
| `STAGING_HOST` | Staging VM IP/hostname |
| `STAGING_USER` | SSH user |
| `STAGING_SSH_KEY` | Private SSH key |
| `PROD_HOST` | Production VM IP |
| `PROD_USER` | SSH user |
| `PROD_SSH_KEY` | Private SSH key |

Optional: `STAGING_SSH_PORT`, `PROD_SSH_PORT` (default 22)

### Repository variables

| Variable | Example |
|----------|---------|
| `GCP_REGION` | `us-central1` |

### Environments

Create GitHub environments **staging** and **production** (Settings → Environments). Add protection rules on **production** (required reviewers, etc.).

## 3. Server setup (each VM)

```bash
sudo apt update && sudo apt install -y docker.io docker-compose-plugin
sudo usermod -aG docker $USER

sudo mkdir -p /opt/todo
sudo chown $USER:$USER /opt/todo
```

### Staging env file

On **staging** server: `/opt/todo/.env.staging` (copy from `.env.staging.example`):

```env
REGISTRY=us-central1-docker.pkg.dev/YOUR_PROJECT/todo
IMAGE_TAG=staging

HTTP_PORT=8080
APP_URL=https://staging.yourdomain.com
APP_DEBUG=false

DB_HOST=10.x.x.x
DB_PORT=3306
DB_DATABASE=todo_staging
DB_USERNAME=...
DB_PASSWORD=...
CORS_ALLOWED_ORIGINS=https://staging.yourdomain.com
```

### Production env file

On **production** server: `/opt/todo/.env.production` (copy from `.env.production.example`).

### Docker login on VM (pull images)

```bash
gcloud auth configure-docker us-central1-docker.pkg.dev
# Or use a dedicated pull service account on the VM
```

## 4. CI/CD workflows

| Workflow | Trigger | Action |
|----------|---------|--------|
| `ci.yml` | PR / push to `staging`, `main` | Test PHP, build React, test Docker builds |
| `deploy.yaml` | Push to `staging` | Build & push images → deploy staging |
| `deploy.yaml` | Push to `main` | Build & push images → deploy production |
| `deploy.yaml` | Manual `workflow_dispatch` | Choose staging or production |

## 5. Local Docker test

```bash
cp .env.local.example .env.local
# Edit DB_HOST if needed (host.docker.internal for XAMPP on Windows/Mac)

docker compose --env-file .env.local up --build
```

Open http://localhost:8080

## 6. Manual deploy on server

```bash
cd /opt/todo
export IMAGE_TAG=staging-abc123
./scripts/deploy-remote.sh staging "$IMAGE_TAG"
```

## Branch strategy

```
feature/* → PR → staging branch → auto deploy staging
staging → PR → main → auto deploy production
```

## Files reference

| File | Purpose |
|------|---------|
| `docker/backend/Dockerfile` | PHP API image |
| `docker/frontend/Dockerfile` | React + nginx image |
| `docker-compose.staging.yml` | Staging stack |
| `docker-compose.prod.yml` | Production stack |
| `.github/workflows/deploy.yaml` | Build, push, SSH deploy |
| `.github/workflows/ci.yml` | CI checks |

## Troubleshooting

- **502 on /api**: Check `api` container logs: `docker logs todo-api-staging`
- **DB connection**: Verify `DB_HOST` reaches Cloud SQL (private IP / proxy)
- **CORS errors**: Set `CORS_ALLOWED_ORIGINS` to your exact frontend URL
- **Pull denied**: Run `gcloud auth configure-docker` on the VM

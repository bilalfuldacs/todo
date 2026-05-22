#!/usr/bin/env bash
# Run on staging/prod server after images are pushed to Artifact Registry.
# Usage: ./scripts/deploy-remote.sh staging|production [image_tag]

set -euo pipefail

ENVIRONMENT="${1:?Usage: deploy-remote.sh staging|production [image_tag]}"
IMAGE_TAG="${2:-staging}"

APP_DIR="${APP_DIR:-/opt/todo}"
cd "$APP_DIR"

case "$ENVIRONMENT" in
  staging)
    COMPOSE_FILE="docker-compose.staging.yml"
    ENV_FILE=".env.staging"
    ;;
  production)
    COMPOSE_FILE="docker-compose.prod.yml"
    ENV_FILE=".env.production"
    ;;
  *)
    echo "Unknown environment: $ENVIRONMENT"
    exit 1
    ;;
esac

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Missing $ENV_FILE on server. Copy from .env.${ENVIRONMENT}.example"
  exit 1
fi

export IMAGE_TAG

# Allow docker compose to authenticate to Artifact Registry (gcloud configured on host)
echo "Deploying $ENVIRONMENT with tag $IMAGE_TAG ..."
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" pull --quiet
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --remove-orphans
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps

echo "Deploy complete."

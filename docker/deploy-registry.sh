#!/bin/bash
# Deploy script for registry-based production pipeline.
# Called from .gitlab/ci/deploy-prod.yml via SSH.
#
# Usage: deploy-registry.sh <tag>
#
# Expected env vars (set by CI before call):
#   REGISTRY_IMAGE    – e.g. registry.gitlab.com/group/project
#   REGISTRY_USER     – deploy token username
#   REGISTRY_PASSWORD – deploy token value
#
# Expected files in the same directory:
#   .env.prod                    – Laravel + Docker env
#   docker-compose.registry.yml  – compose config

set -euo pipefail

TAG="${1:?Usage: deploy-registry.sh <tag>}"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "==> Loading .env.prod..."
source "${SCRIPT_DIR}/.env.prod"

REGISTRY_HOST="$(echo "${REGISTRY_IMAGE}" | cut -d/ -f1)"

echo "==> Logging into registry ${REGISTRY_HOST}..."
echo "${REGISTRY_PASSWORD}" | docker login -u "${REGISTRY_USER}" --password-stdin "${REGISTRY_HOST}"

echo "==> Ensuring Docker networks exist..."
docker network inspect "${DOCKER_NETWORK_NAME}_bridge" >/dev/null 2>&1 || \
  docker network create "${DOCKER_NETWORK_NAME}_bridge"
docker network inspect proxy >/dev/null 2>&1 || \
  docker network create proxy

export TAG

echo "==> Pulling images (tag: ${TAG})..."
docker compose -f "${SCRIPT_DIR}/docker-compose.registry.yml" \
  --env-file="${SCRIPT_DIR}/.env.prod" \
  --project-name="${APP_NAME}" \
  pull

echo "==> Starting containers..."
docker compose -f "${SCRIPT_DIR}/docker-compose.registry.yml" \
  --env-file="${SCRIPT_DIR}/.env.prod" \
  --project-name="${APP_NAME}" \
  up -d

echo "==> Waiting for fpm to be ready..."
sleep 5

echo "==> Running migrations..."
docker exec "${APP_NAME}_fpm" php artisan migrate --force

echo "==> Caching config/routes/views..."
docker exec "${APP_NAME}_fpm" php artisan config:cache
docker exec "${APP_NAME}_fpm" php artisan route:cache
docker exec "${APP_NAME}_fpm" php artisan view:cache
docker exec "${APP_NAME}_fpm" php artisan storage:link 2>/dev/null || true

echo ""
echo "=========================================="
echo "  Deployed ${APP_NAME} @ ${TAG}"
echo "=========================================="

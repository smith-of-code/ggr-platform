#!/bin/bash

if [ -n "$1" ]
then
env_file=".env.$1"
else

echo "./run.sh  no param from ( prod, example, local  )"
exit
fi
echo ./${env_file}

source ./${env_file}

cp ./${env_file} ../.env

docker network inspect ${DOCKER_NETWORK_NAME}_bridge >/dev/null 2>&1 || \
  docker network create ${DOCKER_NETWORK_NAME}_bridge

docker network inspect proxy >/dev/null 2>&1 || \
  docker network create proxy

# Получаем ID пользователя и группы
export USER_ID=$(id -u)
export GROUP_ID=$(id -g)

# Определяем конфигурацию по имени файла
if [[ "$env_file" == *prod* ]]; then
  echo "Запуск с PROD-конфигом (${env_file})..."
  docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file=./${env_file} --project-name=${APP_NAME} up -d --build
else
  echo "Запуск с НЕ-PROD конфигом (${env_file})..."
  docker compose --env-file=./${env_file} --project-name=${APP_NAME} up -d --build
fi


docker exec ${APP_NAME}_fpm composer update
docker exec ${APP_NAME}_fpm php artisan storage:link
docker exec ${APP_NAME}_fpm php artisan migrate
docker exec ${APP_NAME}_fpm npm update --prefer-online
docker exec ${APP_NAME}_fpm npm run build

# Удаляем public/hot чтобы Laravel использовал собранные assets
docker exec ${APP_NAME}_fpm rm -f public/hot

get_port() {
  docker port "$1" "$2" 2>/dev/null | head -1 | sed 's/.*://'
}

NGINX_HTTP=$(get_port ${APP_NAME}_nginx 80)
NGINX_HTTPS=$(get_port ${APP_NAME}_nginx 443)
PG_PORT=$(get_port ${APP_NAME}_postgres 5432)
REDIS_PORT=$(get_port ${APP_NAME}_redis 6379)
MAILPIT_UI=$(get_port ${APP_NAME}_mailpit 8025)

echo ""
echo "=========================================="
echo "  ✅ ${APP_NAME} запущен!"
echo "=========================================="
[ -n "$APP_URL" ]    && echo "  URL:         ${APP_URL}"
[ -n "$NGINX_HTTP" ] && echo "  HTTP:        http://localhost:${NGINX_HTTP}"
[ -n "$NGINX_HTTPS" ] && echo "  HTTPS:       https://localhost:${NGINX_HTTPS}"
[ -n "$PG_PORT" ]    && echo "  PostgreSQL:  localhost:${PG_PORT}"
[ -n "$REDIS_PORT" ] && echo "  Redis:       localhost:${REDIS_PORT}"
[ -n "$MAILPIT_UI" ] && echo "  Mailpit:     http://localhost:${MAILPIT_UI}"
echo "=========================================="


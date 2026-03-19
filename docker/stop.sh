env_file="../.env"

source ${env_file}

ids="$(docker ps --all --quiet --filter "name=^${APP_NAME}")"
[ -n "$ids" ] && docker rm -f $ids

docker network rm  "${DOCKER_NETWORK_NAME}_bridge"


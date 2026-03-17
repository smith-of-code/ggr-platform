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

docker exec ${APP_NAME}_fpm php artisan db:seed

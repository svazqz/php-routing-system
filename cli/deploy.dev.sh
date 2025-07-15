docker compose -f infra/dev/docker-compose.yml down
rm -Rf vendor
rm -f composer.lock
chmod 0777 .
chmod 0777 ./public/assets
docker compose -f infra/dev/docker-compose.yml up -d
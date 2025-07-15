docker compose -f infra/prod/docker-compose.yml down
rm -Rf vendor
rm -f composer.lock
git pull
chmod 0777 .
chmod 0777 ./public/assets
docker compose -f infra/prod/docker-compose.yml up -d
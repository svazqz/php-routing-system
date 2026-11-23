#!/bin/sh
set -eu
cp config.test.ini config.ini
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text=php://stdout tests/Unit
cp config.test.ini config.ini
cp tests/fixtures/Probe.php app/controllers/api/Probe.php
composer dump-autoload --quiet
php -S 127.0.0.1:8080 -t public public/index.php > /tmp/php-test-server.log 2>&1 &
http_process=$!
trap 'kill "$http_process" 2>/dev/null || true' EXIT
for delay in 1 2 3 4 5; do
  if php -r 'exit(@fsockopen("127.0.0.1", 8080, $errno, $error, 1) ? 0 : 1);' > /dev/null 2>&1; then
    break
  fi
  sleep 1
done
if ! php tests/integration.php; then
  cat /tmp/php-test-server.log >&2
  exit 1
fi

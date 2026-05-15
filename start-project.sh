#!/usr/bin/env bash
set -euo pipefail

compose() {
    if docker compose version >/dev/null 2>&1; then
        docker compose "$@"
    else
        docker-compose "$@"
    fi
}

echo "==> Subindo containers"
compose up -d --build

echo "==> Aguardando MySQL ficar pronto"
until compose exec -T mysql mysqladmin ping -h localhost -uroot -proot --silent; do
    sleep 2
done

echo "==> Preparando Laravel"
compose exec -T backend composer install --no-interaction --prefer-dist --optimize-autoloader
compose exec -T backend php artisan key:generate --force
compose exec -T backend php artisan optimize:clear

echo "==> Rodando migrations e seeders"
if [ "${1:-}" = "--fresh" ] || [ "${RESET_DATABASE:-0}" = "1" ]; then
    compose exec -T backend php artisan migrate:fresh --seed --force
else
    compose exec -T backend php artisan migrate --force
    compose exec -T backend php artisan db:seed --force
fi

echo "==> Projeto pronto"
echo "Frontend: http://localhost:3000"
echo "API:      http://localhost:8000/api"

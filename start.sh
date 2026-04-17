#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker n'est pas installe."
    exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
    echo "Docker Compose (v2) n'est pas disponible."
    exit 1
fi

if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env cree depuis .env.example"
fi

docker compose up -d --build

if ! grep -q '^APP_KEY=base64:' .env; then
    docker compose exec -T app php artisan key:generate --force
fi

echo "Attente de la base de donnees..."
MAX_RETRIES=25
COUNTER=0
until docker compose exec -T app php artisan migrate:status >/dev/null 2>&1; do
    COUNTER=$((COUNTER + 1))
    if [ "$COUNTER" -ge "$MAX_RETRIES" ]; then
        echo "La base de donnees ne repond pas a temps."
        exit 1
    fi
    sleep 2
done

docker compose exec -T app php artisan migrate --seed --force
docker compose exec -T app php artisan optimize:clear

echo
echo "Application prete : http://localhost:8000"
echo "Admin : admin@example.com / password"
echo "User  : user@example.com / password"

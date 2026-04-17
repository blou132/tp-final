#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker n'est pas installe."
    exit 1
fi

DOCKER_CMD=(docker)

if ! docker info >/dev/null 2>&1; then
    if command -v sudo >/dev/null 2>&1; then
        echo "Acces Docker avec sudo..."
        sudo docker info >/dev/null
        DOCKER_CMD=(sudo docker)
    else
        echo "Impossible d'acceder au daemon Docker (droits insuffisants)."
        exit 1
    fi
fi

dcompose() {
    "${DOCKER_CMD[@]}" compose "$@"
}

if ! dcompose version >/dev/null 2>&1; then
    echo "Docker Compose (v2) n'est pas disponible."
    exit 1
fi

if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env cree depuis .env.example"
fi

dcompose up -d --build

if ! grep -q '^APP_KEY=base64:' .env; then
    dcompose exec -T app php artisan key:generate --force
fi

echo "Attente de la base de donnees..."
MAX_RETRIES=25
COUNTER=0
until dcompose exec -T app php artisan migrate:status >/dev/null 2>&1; do
    COUNTER=$((COUNTER + 1))
    if [ "$COUNTER" -ge "$MAX_RETRIES" ]; then
        echo "La base de donnees ne repond pas a temps."
        exit 1
    fi
    sleep 2
done

dcompose exec -T app php artisan migrate --seed --force
dcompose exec -T app php artisan optimize:clear

if command -v curl >/dev/null 2>&1; then
    if ! curl -fsS http://localhost:8000 >/dev/null 2>&1; then
        echo "La page http://localhost:8000 ne repond pas encore."
        echo "Logs utiles:"
        dcompose logs --tail=80 nginx app || true
        exit 1
    fi
fi

echo
echo "Application prete : http://localhost:8000"
echo "Admin : admin@example.com / password"
echo "User  : user@example.com / password"

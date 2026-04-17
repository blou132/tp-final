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

upsert_env() {
    local key="$1"
    local value="$2"
    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        echo "${key}=${value}" >> .env
    fi
}

if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env cree depuis .env.example"
fi

upsert_env "APP_URL" "http://localhost"
upsert_env "DB_CONNECTION" "mysql"
upsert_env "DB_HOST" "mysql"
upsert_env "DB_PORT" "3306"
upsert_env "DB_DATABASE" "tp_final"
upsert_env "DB_USERNAME" "tp_user"
upsert_env "DB_PASSWORD" "tp_password"
upsert_env "MONGODB_HOST" "mongodb"
upsert_env "MONGODB_PORT" "27017"
upsert_env "MONGODB_DATABASE" "tp_final_logs"
upsert_env "MONGODB_USERNAME" "root"
upsert_env "MONGODB_PASSWORD" "root"
upsert_env "MONGODB_AUTH_DATABASE" "admin"
upsert_env "VIEW_COMPILED_PATH" "/var/www/html/storage/framework/views"
upsert_env "SESSION_DRIVER" "file"
upsert_env "CACHE_STORE" "file"

if ! grep -q '^APP_KEY=base64:' .env; then
    APP_KEY_VALUE="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    upsert_env "APP_KEY" "$APP_KEY_VALUE"
fi

echo "Reinitialisation des conteneurs/volumes du projet..."
dcompose down -v --remove-orphans >/dev/null 2>&1 || true

dcompose up -d --build

if ! dcompose cp .env app:/var/www/html/.env >/dev/null 2>&1; then
    dcompose exec -T app sh -lc "cat > /var/www/html/.env" < .env
fi

dcompose exec -T app sh -lc "mkdir -p \
storage/framework/cache \
storage/framework/sessions \
storage/framework/views \
storage/logs \
bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache"

VIEW_COMPILED_PATH_IN_CONTAINER="$(
    dcompose exec -T app php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $kernel=$app->make("Illuminate\\Contracts\\Console\\Kernel"); $kernel->bootstrap(); $path=(string) config("view.compiled"); if (trim($path) === "") { fwrite(STDERR, "view.compiled is empty\n"); exit(1); } echo $path;'
)"

dcompose exec -T app sh -lc "mkdir -p \"$VIEW_COMPILED_PATH_IN_CONTAINER\" && chown -R www-data:www-data \"$VIEW_COMPILED_PATH_IN_CONTAINER\""

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
dcompose exec -T app php artisan config:clear
dcompose exec -T app php artisan route:clear
dcompose exec -T app php artisan cache:clear
dcompose exec -T app php artisan event:clear
dcompose exec -T app php artisan view:clear || true

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

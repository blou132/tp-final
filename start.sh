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

lartisan() {
    dcompose exec -T --user www-data app php artisan "$@"
}

build_frontend_assets() {
    echo "Build des assets frontend..."

    "${DOCKER_CMD[@]}" run --rm \
        --user "$(id -u):$(id -g)" \
        -e npm_config_cache=/tmp/npm-cache \
        -v "${ROOT_DIR}:/app" \
        -w /app \
        node:22 \
        sh -lc 'npm ci && npm run build'
}

open_browser() {
    local url="$1"

    if [ -n "${CI:-}" ]; then
        return
    fi

    if [ -z "${DISPLAY:-}" ] && [ -z "${WAYLAND_DISPLAY:-}" ]; then
        echo "Session graphique non detectee. Ouvre manuellement: ${url}"
        return
    fi

    if ! command -v xdg-open >/dev/null 2>&1; then
        echo "xdg-open introuvable. Ouvre manuellement: ${url}"
        return
    fi

    if xdg-open "${url}" >/dev/null 2>&1; then
        echo "Navigateur ouvert: ${url}"
    else
        echo "Impossible d'ouvrir automatiquement le navigateur. Ouvre: ${url}"
    fi
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
upsert_env "APP_LOCALE" "fr"
upsert_env "APP_FALLBACK_LOCALE" "fr"
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
upsert_env "SESSION_DRIVER" "file"
upsert_env "CACHE_STORE" "file"

if ! grep -q '^APP_KEY=base64:' .env; then
    if command -v php >/dev/null 2>&1; then
        APP_KEY_VALUE="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    elif command -v openssl >/dev/null 2>&1; then
        APP_KEY_VALUE="base64:$(openssl rand -base64 32 | tr -d '\n')"
    else
        APP_KEY_VALUE="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
    fi
    upsert_env "APP_KEY" "$APP_KEY_VALUE"
fi

echo "Reinitialisation des conteneurs/volumes du projet..."
dcompose down -v --remove-orphans

build_frontend_assets

echo "Build de l'image applicative..."
dcompose build app

echo "Demarrage des services..."
dcompose up -d --force-recreate

if ! dcompose cp .env app:/var/www/html/.env >/dev/null 2>&1; then
    dcompose exec -T app sh -lc "cat > /var/www/html/.env" < .env
fi

dcompose exec -T app sh -lc "mkdir -p \
storage/framework/cache \
storage/framework/cache/data \
storage/framework/sessions \
storage/framework/views \
storage/logs \
bootstrap/cache \
&& find storage bootstrap/cache -type d -exec chmod 777 {} + \
&& find storage bootstrap/cache -type f -exec chmod 666 {} +"

echo "Attente de MySQL..."
MAX_RETRIES=90
COUNTER=0
until dcompose exec -T mysql sh -lc "mysqladmin ping -h127.0.0.1 -uroot -proot --silent" >/dev/null 2>&1; do
    COUNTER=$((COUNTER + 1))
    if [ "$COUNTER" -ge "$MAX_RETRIES" ]; then
        echo "MySQL ne repond pas a temps."
        dcompose logs --tail=120 mysql || true
        exit 1
    fi
    sleep 2
done

echo "Attente de la connexion Laravel -> MySQL..."
MAX_RETRIES=180
COUNTER=0
until dcompose exec -T --user www-data app php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $kernel=$app->make("Illuminate\\Contracts\\Console\\Kernel"); $kernel->bootstrap(); Illuminate\Support\Facades\DB::select("SELECT 1");' >/dev/null 2>&1; do
    COUNTER=$((COUNTER + 1))
    if [ "$COUNTER" -ge "$MAX_RETRIES" ]; then
        echo "La connexion Laravel vers MySQL ne repond pas a temps."
        dcompose logs --tail=120 app mysql || true
        dcompose exec -T app sh -lc "tail -n 120 storage/logs/laravel.log" || true
        exit 1
    fi
    sleep 2
done

lartisan migrate --seed --force
lartisan config:clear
lartisan route:clear
lartisan cache:clear
lartisan event:clear
lartisan view:clear || true

if command -v curl >/dev/null 2>&1; then
    HTTP_CODE="$(curl -s -o /tmp/tp-final-start-response.html -w '%{http_code}' http://localhost:8000 || true)"
    if [ -z "$HTTP_CODE" ] || [ "$HTTP_CODE" -ge 400 ]; then
        echo "La page http://localhost:8000 ne repond pas encore."
        echo "HTTP code: ${HTTP_CODE:-unknown}"
        echo "Logs utiles:"
        dcompose logs --tail=80 nginx app || true
        dcompose exec -T app sh -lc "tail -n 120 storage/logs/laravel.log" || true
        exit 1
    fi
fi

echo
echo "Application prete : http://localhost:8000"
echo "Admin : admin@example.com / password"
echo "User  : user@example.com / password"

open_browser "http://localhost:8000/"

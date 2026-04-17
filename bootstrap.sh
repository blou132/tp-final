#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

if ! command -v sudo >/dev/null 2>&1; then
    echo "sudo est requis pour installer Docker."
    exit 1
fi

if ! command -v apt >/dev/null 2>&1; then
    echo "Ce script est prevu pour Ubuntu/Debian (apt)."
    exit 1
fi

echo "[1/6] Mise a jour des paquets..."
sudo apt update

echo "[2/6] Installation des prerequis..."
sudo apt install -y ca-certificates curl

if ! command -v docker >/dev/null 2>&1; then
    echo "[3/6] Installation de Docker..."
    curl -fsSL https://get.docker.com | sudo sh
else
    echo "[3/6] Docker est deja installe."
fi

if command -v systemctl >/dev/null 2>&1; then
    sudo systemctl enable --now docker >/dev/null 2>&1 || true
fi

echo "[4/6] Ajout de l'utilisateur au groupe docker..."
sudo usermod -aG docker "$USER" || true

echo "[5/6] Verification Docker..."
sudo docker --version
sudo docker compose version

echo "[6/6] Lancement automatique de l'application..."
./start.sh

#!/usr/bin/env bash
# Lance les trois services en local avec le serveur intégré de PHP (pour développer ou faire une démonstration).
#   auth -> http://127.0.0.1:8001   api -> http://127.0.0.1:8002   web -> http://127.0.0.1:8003
# Prérequis : PHP 8.1+ avec pdo_mysql et curl, bases importées, fichiers .env créés (voir README.md).
set -euo pipefail
racine="$(cd "$(dirname "$0")/.." && pwd)"
for service in auth api web; do
  [ -f "$racine/$service/.env" ] || { echo "Il manque $service/.env (copiez $service/.env.example)"; exit 1; }
done
php -S 127.0.0.1:8001 -t "$racine/auth" &
php -S 127.0.0.1:8002 -t "$racine/api" &
php -S 127.0.0.1:8003 -t "$racine/web/src" &
echo "Application : http://127.0.0.1:8003  (Ctrl+C pour tout arrêter)"
trap 'kill 0' INT TERM
wait

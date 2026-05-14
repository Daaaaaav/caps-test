#!/usr/bin/env bash
set -euo pipefail

ACTION="${1:-help}"
MODE="${2:-}"

COMPOSE_BASE=(docker compose)
COMPOSE_WAZUH=(docker compose --profile wazuh)

ensure_env() {
  if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env from .env.example"
  fi
}

set_env_value() {
  local key="$1"
  local value="$2"

  if grep -q "^${key}=" .env; then
    awk -v k="$key" -v v="$value" 'index($0, k"=") == 1 {$0 = k"="v} {print}' .env > .env.tmp
    mv .env.tmp .env
  else
    echo "${key}=${value}" >> .env
  fi
}

set_docker_env() {
  set_env_value "DB_CONNECTION" "mysql"
  set_env_value "DB_HOST" "mysql"
  set_env_value "DB_PORT" "3306"
  set_env_value "WAZUH_ALERT_LOG_PATH" "/var/ossec/logs/alerts/alerts.log"
}

start_app() {
  set_docker_env

  if [ "$MODE" = "--wazuh" ]; then
    "${COMPOSE_WAZUH[@]}" down --remove-orphans
  else
    "${COMPOSE_BASE[@]}" down --remove-orphans
  fi

  if [ "$MODE" = "--wazuh" ]; then
    "${COMPOSE_WAZUH[@]}" up -d app queue vite mysql wazuh-manager
  else
    "${COMPOSE_BASE[@]}" up -d app queue vite mysql
  fi

  "${COMPOSE_BASE[@]}" exec -T app php artisan migrate --force --seed --seeder=DatabaseSeeder

  if ! "${COMPOSE_BASE[@]}" ps --status running app | grep -q "app"; then
    echo "App container is not running. Showing recent app logs:"
    "${COMPOSE_BASE[@]}" logs --tail=80 app
    exit 1
  fi
}

print_usage() {
  cat <<'EOF'
Usage:
  ./scripts/docker-dev.sh init
  ./scripts/docker-dev.sh start [--wazuh]
  ./scripts/docker-dev.sh stop [--volumes]
  ./scripts/docker-dev.sh logs [--wazuh]
  ./scripts/docker-dev.sh test

Commands:
  init           Prepare .env, install dependencies, generate app key, migrate DB.
  start          Start app services; add --wazuh to include wazuh-manager.
  stop           Stop services; add --volumes to remove DB volume.
  logs           Follow app/queue/mysql logs; add --wazuh for Wazuh logs too.
  test           Run Laravel test suite in Docker.
EOF
}

case "$ACTION" in
  init)
    ensure_env
    set_docker_env
    "${COMPOSE_BASE[@]}" run --rm composer install
    "${COMPOSE_BASE[@]}" run --rm npm install
    "${COMPOSE_BASE[@]}" run --rm artisan key:generate
    "${COMPOSE_BASE[@]}" up -d mysql
    "${COMPOSE_BASE[@]}" run --rm artisan migrate
    echo "Initialization complete. Run ./scripts/docker-dev.sh start"
    ;;
  start)
    ensure_env
    start_app
    echo "App: http://localhost:8000"
    echo "Vite: http://localhost:5173"
    ;;
  stop)
    if [ "$MODE" = "--volumes" ]; then
      "${COMPOSE_BASE[@]}" down -v
    else
      "${COMPOSE_BASE[@]}" down
    fi
    ;;
  logs)
    if [ "$MODE" = "--wazuh" ]; then
      "${COMPOSE_WAZUH[@]}" logs -f app queue mysql wazuh-manager
    else
      "${COMPOSE_BASE[@]}" logs -f app queue mysql
    fi
    ;;
  test)
    "${COMPOSE_BASE[@]}" run --rm artisan test
    ;;
  *)
    print_usage
    ;;
esac

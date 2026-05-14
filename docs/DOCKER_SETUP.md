# Docker Setup (Laravel + Optional Wazuh)

This project can run in Docker with two modes:

- App only (recommended for most devs, including Windows)
- App + Wazuh manager (optional profile)

On Fedora/RHEL with SELinux enabled, bind mounts are configured with `:z` labels in compose so services can read project files.

## 1. Prerequisites

- Docker Desktop (Windows/macOS) or Docker Engine + Compose plugin (Linux)
- Open ports: `8000`, `5173`, `3307` (default MySQL host port), `1516` (default Wazuh manager host port)

## 2. Prepare Environment

From project root:

```bash
cp .env.example .env
```

Set Docker database values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=caps_test
DB_USERNAME=caps_user
DB_PASSWORD=caps_password
```

Optional Wazuh log path (default is already this path):

```env
WAZUH_ALERT_LOG_PATH=/var/ossec/logs/alerts/alerts.log
```

Optional MySQL host port override (container still uses 3306 internally):

```env
MYSQL_HOST_PORT=3307

Optional Wazuh host port overrides:

```env
WAZUH_AGENT_HOST_PORT=1514
WAZUH_MANAGER_HOST_PORT=1516
```
```

## 3. Install Dependencies (inside Docker)

```bash
docker compose run --rm composer install
docker compose run --rm npm install
docker compose run --rm artisan key:generate
docker compose run --rm artisan migrate
```

## 4. One-Command Helper Scripts (Recommended)

Linux/macOS:

```bash
chmod +x scripts/docker-dev.sh
./scripts/docker-dev.sh init
./scripts/docker-dev.sh start
```

Enable Wazuh optionally:

```bash
./scripts/docker-dev.sh start --wazuh
```

Windows PowerShell:

```powershell
.\scripts\docker-dev.ps1 -Action init
.\scripts\docker-dev.ps1 -Action start
```

Enable Wazuh optionally:

```powershell
.\scripts\docker-dev.ps1 -Action start -Wazuh
```

Other helper commands:

- `./scripts/docker-dev.sh stop --volumes`
- `./scripts/docker-dev.sh logs --wazuh`
- `./scripts/docker-dev.sh test`
- `.\scripts\docker-dev.ps1 -Action stop -Volumes`
- `.\scripts\docker-dev.ps1 -Action logs -Wazuh`
- `.\scripts\docker-dev.ps1 -Action test`

## 5. Start Project (without Wazuh)

```bash
docker compose up -d app queue vite mysql
```

Open:

- App: http://localhost:8000
- Vite: http://localhost:5173

## 6. Start Project (with optional Wazuh)

```bash
docker compose --profile wazuh up -d app queue vite mysql wazuh-manager
```

Notes:

- If your team cannot run Wazuh, skip profile `wazuh` and app still works.
- Security page falls back to `storage/logs/laravel.log` when Wazuh alerts are unavailable.

## 7. Useful Commands

Run tests:

```bash
docker compose run --rm artisan test
```

Tail logs:

```bash
docker compose logs -f app
docker compose logs -f queue
docker compose logs -f mysql
```

Stop all services:

```bash
docker compose down
```

Reset DB volume:

```bash
docker compose down -v
```

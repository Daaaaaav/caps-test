# CAPS Test - Run Guide

This project supports two ways to run:

- Docker (recommended for Linux/macOS/Windows)
- Native local setup (Fedora/Linux focused)

Wazuh is optional in both flows.

## 1. Clone Project

```bash
git clone <your-repo-url>
cd caps-test
```

## 2. Docker Quick Start (Recommended)

Use Docker if your team needs the same setup across Windows and Linux.

Note for Fedora/RHEL users: bind mounts use SELinux relabel (`:z`) in compose so containers can read project files.

### Linux/macOS (helper script)

```bash
chmod +x scripts/docker-dev.sh
./scripts/docker-dev.sh init
./scripts/docker-dev.sh start
```

### Windows PowerShell (helper script)

```powershell
.\scripts\docker-dev.ps1 -Action init
.\scripts\docker-dev.ps1 -Action start
```

Open:

- App: http://localhost:8000
- Vite: http://localhost:5173
- MySQL (host): localhost:3307

If `3307` is busy too, set a different value in `.env` before start:

```env
MYSQL_HOST_PORT=3310
```

### Start with Wazuh (optional)

Linux/macOS:

```bash
./scripts/docker-dev.sh start --wazuh
```

Windows PowerShell:

```powershell
.\scripts\docker-dev.ps1 -Action start -Wazuh
```

### Common Docker helper commands

Linux/macOS:

```bash
./scripts/docker-dev.sh logs
./scripts/docker-dev.sh logs --wazuh
./scripts/docker-dev.sh test
./scripts/docker-dev.sh stop
./scripts/docker-dev.sh stop --volumes
```

Windows PowerShell:

```powershell
.\scripts\docker-dev.ps1 -Action logs
.\scripts\docker-dev.ps1 -Action logs -Wazuh
.\scripts\docker-dev.ps1 -Action test
.\scripts\docker-dev.ps1 -Action stop
.\scripts\docker-dev.ps1 -Action stop -Volumes
```

Manual Docker commands are documented in docs/DOCKER_SETUP.md.

## 3. Native Local Setup (Fedora/Linux)

Use this if you want to run without Docker.

### Prerequisites

- PHP 8.2+
- Composer 2+
- Node.js and npm
- MariaDB or MySQL
- Git

Example Fedora install command:

```bash
sudo dnf install -y php php-cli php-mbstring php-xml php-pdo php-mysqlnd php-bcmath php-curl php-zip unzip composer nodejs npm mariadb-server git ripgrep
```

Start and enable MariaDB:

```bash
sudo systemctl enable --now mariadb
```

### Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE caps_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'caps_user'@'localhost' IDENTIFIED BY 'change_this_password';
GRANT ALL PRIVILEGES ON caps_test.* TO 'caps_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Environment and Install

```bash
cp .env.example .env
```

Set database values in .env:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=caps_test
DB_USERNAME=caps_user
DB_PASSWORD=change_this_password
```

Install and initialize:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
```

Run app:

```bash
composer run dev
```

Or run services separately:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

## 4. Wazuh (Optional)

If Wazuh is not available, the security report page can still run by reading Laravel logs.

Fedora install script:

```bash
chmod +x scripts/wazuh_install_manager_fedora.sh
./scripts/wazuh_install_manager_fedora.sh
```

Test simulation:

```bash
chmod +x scripts/wazuh_test_simulation.sh
./scripts/wazuh_test_simulation.sh
```

Check alerts:

```bash
sudo tail -f /var/ossec/logs/alerts/alerts.log
```

One-command web test:

```bash
chmod +x scripts/run_wazuh_web_test.sh scripts/wazuh_test_simulation.sh
./scripts/run_wazuh_web_test.sh
```

## 5. Useful Commands

```bash
php artisan test
php artisan optimize:clear
tail -f storage/logs/laravel.log
```

## 6. Notes

- Docker is the easiest cross-platform flow for teams.
- Keep secrets and real credentials out of git.
- Update PROJECT_PATH in Wazuh scripts if your local path is different.

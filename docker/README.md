# Docker Deployment Guide

This guide explains how to deploy the KRB System with Docker, including the Laravel application and Python LSTM service.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- At least 4GB RAM available
- 10GB free disk space

## Quick Start

### 1. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit `.env` and set the following required variables:

```env
APP_NAME="KRB System"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=krbs
DB_USERNAME=krbs_user
DB_PASSWORD=your_secure_password

SYSTEM_MODE=deployment

# LSTM Service URL (internal Docker network)
LSTM_SERVICE_URL=http://lstm-service:8001
```

### 2. Generate Application Key

If you don't have an `APP_KEY`, generate one:

```bash
docker run --rm -v $(pwd):/app -w /app php:8.3-cli php artisan key:generate --show
```

Copy the output and set it in your `.env` file.

### 3. Build and Start Services

Build all Docker images:

```bash
docker-compose build
```

Start all services:

```bash
docker-compose up -d
```

### 4. Initialize Database

Run migrations:

```bash
docker-compose exec app php artisan migrate --force
```

Seed the database (optional):

```bash
docker-compose exec app php artisan db:seed --force
```

### 5. Verify Services

Check that all services are running:

```bash
docker-compose ps
```

You should see:
- `krb-mysql` - MySQL database
- `krb-lstm` - Python LSTM service
- `krb-app` - Laravel application
- `krb-nginx` - Nginx web server

Test the LSTM service:

```bash
curl http://localhost:8001/
```

Expected response:
```json
{
  "status": "healthy",
  "service": "Improved LSTM Forecast Service",
  "version": "2.0.0"
}
```

### 6. Access Application

Open your browser and navigate to:
- Application: `http://localhost` (or your configured domain)
- LSTM API Docs: `http://localhost:8001/docs`

## Service Architecture

```
┌─────────────────────────────────────────────────────────┐
│                     Docker Network                       │
│                                                          │
│  ┌──────────┐      ┌──────────┐      ┌──────────┐     │
│  │  Nginx   │─────▶│ Laravel  │─────▶│  MySQL   │     │
│  │  :80     │      │ PHP-FPM  │      │  :3306   │     │
│  └──────────┘      │  :9000   │      └──────────┘     │
│                    └──────────┘                         │
│                         │                               │
│                         ▼                               │
│                    ┌──────────┐                         │
│                    │  LSTM    │                         │
│                    │ Service  │                         │
│                    │  :8001   │                         │
│                    └──────────┘                         │
└─────────────────────────────────────────────────────────┘
```

## Container Details

### MySQL Container
- **Image**: `mysql:8.0`
- **Port**: `3307:3306` (host:container)
- **Volume**: `mysql_data` (persistent storage)
- **Health Check**: MySQL ping every 10s

### LSTM Service Container
- **Build**: Multi-stage from Dockerfile (target: lstm-service)
- **Port**: `8001:8001`
- **Dependencies**: TensorFlow, FastAPI, scikit-learn
- **Health Check**: HTTP GET to `/` every 30s

### Laravel App Container
- **Build**: Multi-stage from Dockerfile (target: laravel)
- **Port**: Internal only (9000)
- **Processes**: PHP-FPM, Queue Workers, Scheduler
- **Managed by**: Supervisor

### Nginx Container
- **Image**: `nginx:alpine`
- **Port**: `80:80`
- **Configuration**: Custom config in `docker/nginx/default.conf`

## Common Commands

### View Logs

All services:
```bash
docker-compose logs -f
```

Specific service:
```bash
docker-compose logs -f app
docker-compose logs -f lstm-service
docker-compose logs -f mysql
```

### Execute Commands in Container

Laravel artisan commands:
```bash
docker-compose exec app php artisan [command]
```

Examples:
```bash
# Clear cache
docker-compose exec app php artisan cache:clear

# Run migrations
docker-compose exec app php artisan migrate

# Create user
docker-compose exec app php artisan tinker
```

### Restart Services

Restart all:
```bash
docker-compose restart
```

Restart specific service:
```bash
docker-compose restart app
docker-compose restart lstm-service
```

### Stop Services

Stop all services:
```bash
docker-compose down
```

Stop and remove volumes (deletes database):
```bash
docker-compose down -v
```

### Update Application

Pull latest code:
```bash
git pull origin main
```

Rebuild and restart:
```bash
docker-compose build app
docker-compose up -d app
```

Run migrations:
```bash
docker-compose exec app php artisan migrate --force
```

## Production Deployment

### Security Checklist

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Use strong `DB_PASSWORD`
- [ ] Configure `APP_URL` to your domain
- [ ] Set `SYSTEM_MODE=deployment` (enables OTP and CAPTCHA)
- [ ] Configure SSL/TLS certificates (use reverse proxy like Traefik or Caddy)
- [ ] Set up firewall rules
- [ ] Configure backup strategy for MySQL volume
- [ ] Set up monitoring and logging

### SSL/TLS Configuration

For production, use a reverse proxy like Traefik or Caddy to handle SSL:

**Example with Traefik:**

Add to `docker-compose.yml`:

```yaml
services:
  nginx:
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.krb.rule=Host(`your-domain.com`)"
      - "traefik.http.routers.krb.entrypoints=websecure"
      - "traefik.http.routers.krb.tls.certresolver=letsencrypt"
```

### Backup Strategy

Backup MySQL data:
```bash
docker-compose exec mysql mysqldump -u root -p krbs > backup_$(date +%Y%m%d).sql
```

Backup storage files:
```bash
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/
```

### Monitoring

Monitor container health:
```bash
docker-compose ps
docker stats
```

Monitor LSTM service:
```bash
curl http://localhost:8001/
```

## Troubleshooting

### LSTM Service Not Starting

Check logs:
```bash
docker-compose logs lstm-service
```

Common issues:
- Insufficient memory (TensorFlow requires ~2GB)
- Port 8001 already in use

### Database Connection Failed

Check MySQL is healthy:
```bash
docker-compose ps mysql
```

Test connection:
```bash
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Permission Errors

Fix storage permissions:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/storage
docker-compose exec app chmod -R 755 /var/www/storage
```

### Application Not Loading

Check Nginx logs:
```bash
docker-compose logs nginx
```

Check PHP-FPM logs:
```bash
docker-compose exec app tail -f /var/log/supervisor/php-fpm.log
```

## Environment Variables Reference

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_ENV` | `production` | Application environment |
| `APP_DEBUG` | `false` | Debug mode |
| `APP_URL` | `http://localhost` | Application URL |
| `DB_HOST` | `mysql` | Database host (container name) |
| `DB_DATABASE` | `krbs` | Database name |
| `DB_USERNAME` | `krbs_user` | Database user |
| `DB_PASSWORD` | `secret` | Database password |
| `MYSQL_HOST_PORT` | `3307` | MySQL host port |
| `APP_PORT` | `80` | Application host port |
| `LSTM_SERVICE_URL` | `http://lstm-service:8001` | LSTM service URL |
| `SYSTEM_MODE` | `deployment` | System mode (development/deployment) |

## Support

For issues or questions:
1. Check logs: `docker-compose logs -f`
2. Verify all services are healthy: `docker-compose ps`
3. Review this documentation
4. Check Laravel logs: `storage/logs/laravel.log`

## License

This deployment configuration is part of the KRB System project.

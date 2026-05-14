# Docker Setup Complete ✅

The KRB System is now fully configured for Docker deployment!

## What Was Created

### Docker Configuration Files

1. **`Dockerfile`** - Multi-stage build for Laravel + Python LSTM service
2. **`docker-compose.yml`** - Orchestrates all services (App, MySQL, LSTM, Nginx)
3. **`.dockerignore`** - Excludes unnecessary files from Docker builds

### Docker Support Files

4. **`docker/nginx/default.conf`** - Nginx web server configuration
5. **`docker/supervisor/supervisord.conf`** - Process manager (PHP-FPM, Queue, Scheduler)
6. **`docker/php/php-fpm.conf`** - PHP-FPM pool configuration
7. **`docker/php/php.ini`** - PHP runtime settings (opcache, limits, etc.)

### Deployment Scripts

8. **`deploy.bat`** - Automated deployment for Windows
9. **`deploy.sh`** - Automated deployment for Linux/macOS

### Documentation

10. **`DEPLOYMENT.md`** - Complete deployment guide with troubleshooting
11. **`docker/README.md`** - Detailed Docker configuration reference
12. **`DOCKER_QUICKSTART.md`** - 5-minute quick start guide

### Environment Configuration

13. **`.env.example`** - Updated with Docker-specific variables:
    - `LSTM_SERVICE_URL` - LSTM service endpoint
    - `APP_PORT` - Application port mapping

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                   Docker Network                         │
│                                                          │
│  ┌──────────┐      ┌──────────┐      ┌──────────┐     │
│  │  Nginx   │─────▶│ Laravel  │─────▶│  MySQL   │     │
│  │  :80     │      │ PHP-FPM  │      │  :3306   │     │
│  └──────────┘      │  :9000   │      └──────────┘     │
│                    │          │                         │
│                    │ Supervisor:                        │
│                    │ - PHP-FPM                          │
│                    │ - Queue Workers (2)                │
│                    │ - Scheduler                        │
│                    └──────────┘                         │
│                         │                               │
│                         ▼                               │
│                    ┌──────────┐                         │
│                    │  LSTM    │                         │
│                    │ Service  │                         │
│                    │  :8001   │                         │
│                    │          │                         │
│                    │ FastAPI + TensorFlow               │
│                    └──────────┘                         │
└─────────────────────────────────────────────────────────┘
```

## Services

| Service | Container | Image | Port | Purpose |
|---------|-----------|-------|------|---------|
| **Nginx** | krb-nginx | nginx:alpine | 80 | Web server |
| **Laravel** | krb-app | Custom (PHP 8.3) | 9000 | Application |
| **MySQL** | krb-mysql | mysql:8.0 | 3307 | Database |
| **LSTM** | krb-lstm | Custom (Python 3.12) | 8001 | AI predictions |

## Key Features

### 🚀 Auto-Start AI Services

The LSTM service automatically starts with the application:
- No manual Python service startup needed
- Health checks ensure service availability
- Automatic restart on failure
- Fallback predictions if service unavailable

### 🔒 Production-Ready

- **Security**: Disabled dangerous PHP functions, secure headers
- **Performance**: Opcache enabled, optimized PHP-FPM pools
- **Reliability**: Supervisor manages all processes
- **Monitoring**: Health checks for all services

### 📦 Complete Isolation

- All services run in isolated containers
- No conflicts with local development tools
- Easy to deploy to any Docker-compatible host
- Consistent environment across dev/staging/production

### 🔄 Automatic Process Management

Supervisor manages:
- **PHP-FPM**: Application processor
- **Queue Workers**: 2 workers for background jobs
- **Scheduler**: Runs Laravel scheduled tasks every minute

### 💾 Persistent Data

- MySQL data stored in Docker volume
- Laravel storage mounted from host
- Survives container restarts

## How to Deploy

### Quick Start (Recommended)

**Windows:**
```bash
.\deploy.bat
```

**Linux/macOS:**
```bash
chmod +x deploy.sh
./deploy.sh
```

### Manual Deployment

```bash
# 1. Configure environment
cp .env.example .env
# Edit .env with your settings

# 2. Build images
docker-compose build

# 3. Start services
docker-compose up -d

# 4. Run migrations
docker-compose exec app php artisan migrate --force

# 5. Optimize
docker-compose exec app php artisan config:cache
```

## Access Points

After deployment:

| Service | URL | Description |
|---------|-----|-------------|
| **Application** | http://localhost | Main web interface |
| **LSTM API** | http://localhost:8001 | AI prediction endpoint |
| **API Docs** | http://localhost:8001/docs | Interactive API documentation |
| **MySQL** | localhost:3307 | Database (external) |

## Environment Variables

### Required Configuration

```env
# Application
APP_KEY=base64:...                    # Generate with: php artisan key:generate
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database
DB_HOST=mysql                         # Docker service name
DB_DATABASE=krbs
DB_USERNAME=krbs_user
DB_PASSWORD=your_secure_password

# System Mode
SYSTEM_MODE=deployment                # Enables OTP and CAPTCHA

# LSTM Service
LSTM_SERVICE_URL=http://lstm-service:8001  # Docker internal network
```

### Optional Configuration

```env
# Ports
MYSQL_HOST_PORT=3307                  # External MySQL port
APP_PORT=80                           # External application port

# CAPTCHA (if SYSTEM_MODE=deployment)
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET=your_secret_key
```

## Common Commands

### Service Management

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart services
docker-compose restart

# View status
docker-compose ps

# View logs
docker-compose logs -f
```

### Application Management

```bash
# Run artisan commands
docker-compose exec app php artisan [command]

# Clear cache
docker-compose exec app php artisan cache:clear

# Run migrations
docker-compose exec app php artisan migrate

# Access tinker
docker-compose exec app php artisan tinker
```

### Database Management

```bash
# Access MySQL CLI
docker-compose exec mysql mysql -u root -p

# Backup database
docker-compose exec mysql mysqldump -u root -p krbs > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u root -p krbs < backup.sql
```

## Verification Checklist

After deployment, verify:

- [ ] All services show "Up (healthy)" status: `docker-compose ps`
- [ ] LSTM service responds: `curl http://localhost:8001/`
- [ ] Application loads: Open http://localhost in browser
- [ ] Can login with default credentials
- [ ] LSTM predictions page shows data (green badge = LSTM active)
- [ ] No errors in logs: `docker-compose logs`

## Production Checklist

Before deploying to production:

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Use strong `DB_PASSWORD` (16+ characters)
- [ ] Set `SYSTEM_MODE=deployment`
- [ ] Configure SSL/TLS (use Traefik or Caddy)
- [ ] Set up automated backups
- [ ] Configure monitoring
- [ ] Set up log rotation
- [ ] Test disaster recovery

## Troubleshooting

### LSTM Service Not Starting

```bash
# Check logs
docker-compose logs lstm-service

# Check memory (TensorFlow needs ~2GB)
docker stats

# Restart service
docker-compose restart lstm-service
```

### Database Connection Failed

```bash
# Check MySQL health
docker-compose ps mysql

# Wait for MySQL to be ready
docker-compose logs mysql

# Restart app
docker-compose restart app
```

### Permission Errors

```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data /var/www/storage
docker-compose exec app chmod -R 755 /var/www/storage
```

### Port Already in Use

Edit `.env`:
```env
APP_PORT=8080
```

Then restart:
```bash
docker-compose down
docker-compose up -d
```

## Documentation

- **Quick Start**: [DOCKER_QUICKSTART.md](DOCKER_QUICKSTART.md) - Get running in 5 minutes
- **Full Guide**: [DEPLOYMENT.md](DEPLOYMENT.md) - Complete deployment documentation
- **Docker Details**: [docker/README.md](docker/README.md) - Configuration reference

## What's Next?

1. **Deploy**: Run `deploy.bat` or `deploy.sh`
2. **Test**: Verify all services are working
3. **Configure**: Set up SSL, backups, monitoring
4. **Go Live**: Deploy to production server

## Support

If you encounter issues:

1. Check logs: `docker-compose logs -f`
2. Verify service health: `docker-compose ps`
3. Review documentation: [DEPLOYMENT.md](DEPLOYMENT.md)
4. Check Laravel logs: `docker-compose exec app tail -f storage/logs/laravel.log`

---

## Summary

✅ **Docker configuration complete**
✅ **LSTM service auto-starts with application**
✅ **Production-ready setup**
✅ **Automated deployment scripts**
✅ **Complete documentation**

**Ready to deploy!** Run `deploy.bat` (Windows) or `deploy.sh` (Linux/macOS) to get started.

---

*Created: May 14, 2026*
*KRB System - Docker Deployment*

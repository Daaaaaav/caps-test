@echo off
REM ============================================================================
REM KRB System - Docker Deployment Script (Windows)
REM ============================================================================

echo ==========================================
echo   KRB System - Docker Deployment
echo ==========================================
echo.

REM Check if Docker is installed
docker --version >nul 2>&1
if errorlevel 1 (
    echo Error: Docker is not installed
    echo Please install Docker Desktop: https://docs.docker.com/desktop/install/windows-install/
    pause
    exit /b 1
)

REM Check if Docker Compose is installed
docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo Error: Docker Compose is not installed
    echo Please install Docker Compose or use Docker Desktop which includes it
    pause
    exit /b 1
)

REM Check if .env file exists
if not exist .env (
    echo Warning: .env file not found
    echo Copying .env.example to .env...
    copy .env.example .env
    echo Created .env file
    echo.
    echo Please edit .env and configure the following:
    echo   - APP_KEY
    echo   - DB_PASSWORD
    echo   - APP_URL
    echo   - SYSTEM_MODE=deployment
    echo.
    pause
)

echo.
echo Step 1: Building Docker images...
echo This may take several minutes on first run...
docker-compose build

if errorlevel 1 (
    echo Error: Failed to build Docker images
    pause
    exit /b 1
)

echo.
echo Docker images built successfully
echo.

echo Step 2: Starting services...
docker-compose up -d

if errorlevel 1 (
    echo Error: Failed to start services
    pause
    exit /b 1
)

echo.
echo Step 3: Waiting for services to be healthy...
timeout /t 10 /nobreak >nul

echo Checking MySQL...
:check_mysql
docker-compose exec -T mysql mysqladmin ping -h localhost --silent >nul 2>&1
if errorlevel 1 (
    echo Waiting for MySQL...
    timeout /t 2 /nobreak >nul
    goto check_mysql
)
echo MySQL is ready

echo Checking LSTM service...
curl -f http://localhost:8001/ >nul 2>&1
if errorlevel 1 (
    echo Warning: LSTM service not responding (will use fallback)
) else (
    echo LSTM service is ready
)

echo.
echo Step 4: Running database migrations...
docker-compose exec -T app php artisan migrate --force

if errorlevel 1 (
    echo Error: Database migration failed
    pause
    exit /b 1
)

echo.
echo Database migrations completed

echo.
echo Step 5: Optimizing application...
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

echo.
echo Application optimized

echo.
echo ==========================================
echo   Deployment Complete!
echo ==========================================
echo.
echo Services:
echo   - Application: http://localhost
echo   - LSTM API: http://localhost:8001
echo   - LSTM Docs: http://localhost:8001/docs
echo   - MySQL: localhost:3307
echo.
echo Useful commands:
echo   - View logs: docker-compose logs -f
echo   - Stop services: docker-compose down
echo   - Restart services: docker-compose restart
echo   - Run artisan: docker-compose exec app php artisan [command]
echo.
echo For more information, see: docker\README.md
echo.
pause

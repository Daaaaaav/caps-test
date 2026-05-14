#!/bin/bash

# ============================================================================
# KRB System - Docker Deployment Script
# ============================================================================

set -e

echo "=========================================="
echo "  KRB System - Docker Deployment"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed${NC}"
    echo "Please install Docker first: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}Error: Docker Compose is not installed${NC}"
    echo "Please install Docker Compose first: https://docs.docker.com/compose/install/"
    exit 1
fi

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Warning: .env file not found${NC}"
    echo "Copying .env.example to .env..."
    cp .env.example .env
    echo -e "${GREEN}✓ Created .env file${NC}"
    echo ""
    echo -e "${YELLOW}Please edit .env and configure the following:${NC}"
    echo "  - APP_KEY (run: docker run --rm -v \$(pwd):/app -w /app php:8.3-cli php artisan key:generate --show)"
    echo "  - DB_PASSWORD"
    echo "  - APP_URL"
    echo "  - SYSTEM_MODE=deployment"
    echo ""
    read -p "Press Enter after configuring .env to continue..."
fi

# Check if APP_KEY is set
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "${YELLOW}Warning: APP_KEY not set in .env${NC}"
    echo "Generating APP_KEY..."
    APP_KEY=$(docker run --rm -v $(pwd):/app -w /app php:8.3-cli php artisan key:generate --show)
    sed -i "s|APP_KEY=|APP_KEY=$APP_KEY|g" .env
    echo -e "${GREEN}✓ Generated APP_KEY${NC}"
fi

echo ""
echo "Step 1: Building Docker images..."
echo "This may take several minutes on first run..."
docker-compose build

echo ""
echo -e "${GREEN}✓ Docker images built successfully${NC}"

echo ""
echo "Step 2: Starting services..."
docker-compose up -d

echo ""
echo "Step 3: Waiting for services to be healthy..."
sleep 10

# Check MySQL health
echo "Checking MySQL..."
for i in {1..30}; do
    if docker-compose exec -T mysql mysqladmin ping -h localhost --silent; then
        echo -e "${GREEN}✓ MySQL is ready${NC}"
        break
    fi
    if [ $i -eq 30 ]; then
        echo -e "${RED}Error: MySQL failed to start${NC}"
        docker-compose logs mysql
        exit 1
    fi
    echo "Waiting for MySQL... ($i/30)"
    sleep 2
done

# Check LSTM service health
echo "Checking LSTM service..."
for i in {1..30}; do
    if curl -f http://localhost:8001/ &> /dev/null; then
        echo -e "${GREEN}✓ LSTM service is ready${NC}"
        break
    fi
    if [ $i -eq 30 ]; then
        echo -e "${YELLOW}Warning: LSTM service not responding (will use fallback)${NC}"
        break
    fi
    echo "Waiting for LSTM service... ($i/30)"
    sleep 2
done

echo ""
echo "Step 4: Running database migrations..."
docker-compose exec -T app php artisan migrate --force

echo ""
echo -e "${GREEN}✓ Database migrations completed${NC}"

echo ""
echo "Step 5: Optimizing application..."
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

echo ""
echo -e "${GREEN}✓ Application optimized${NC}"

echo ""
echo "=========================================="
echo -e "${GREEN}  Deployment Complete!${NC}"
echo "=========================================="
echo ""
echo "Services:"
echo "  - Application: http://localhost"
echo "  - LSTM API: http://localhost:8001"
echo "  - LSTM Docs: http://localhost:8001/docs"
echo "  - MySQL: localhost:3307"
echo ""
echo "Useful commands:"
echo "  - View logs: docker-compose logs -f"
echo "  - Stop services: docker-compose down"
echo "  - Restart services: docker-compose restart"
echo "  - Run artisan: docker-compose exec app php artisan [command]"
echo ""
echo "For more information, see: docker/README.md"
echo ""

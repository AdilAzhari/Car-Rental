# Docker Deployment Guide

This guide covers deploying the Car Rental System using Docker and Docker Compose.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- At least 4GB of available RAM
- At least 10GB of available disk space

## Quick Start

### 1. Clone Repository

```bash
git clone <repository-url> car-rental
cd car-rental
```

### 2. Configure Environment

```bash
# Copy environment file
cp .env.production.example .env

# Edit .env with your production settings
nano .env
```

Important environment variables for Docker:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=car_rental
DB_USERNAME=car_rental
DB_PASSWORD=your_secure_password

REDIS_HOST=redis
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. Build and Start Containers

```bash
# Build images
docker-compose build

# Start containers in background
docker-compose up -d

# View logs
docker-compose logs -f
```

### 4. Initialize Application

```bash
# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate --force

# Optimize for production
docker-compose exec app php artisan optimize:production

# Create storage link
docker-compose exec app php artisan storage:link
```

### 5. Access Application

The application will be available at: http://localhost:8080

## Container Architecture

The Docker setup includes the following containers:

- **app** - Main application container (Nginx + PHP-FPM)
- **mysql** - Database server (MySQL 8.0)
- **redis** - Cache and queue backend
- **queue** - Queue worker for background jobs
- **scheduler** - Laravel task scheduler

## Docker Commands

### Container Management

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# Restart containers
docker-compose restart

# View running containers
docker-compose ps

# View logs
docker-compose logs -f [service-name]

# Execute commands in container
docker-compose exec app bash
docker-compose exec app php artisan [command]
```

### Application Management

```bash
# Clear cache
docker-compose exec app php artisan cache:clear

# Run migrations
docker-compose exec app php artisan migrate --force

# Seed database
docker-compose exec app php artisan db:seed

# Optimize application
docker-compose exec app php artisan optimize:production

# Access Laravel Tinker
docker-compose exec app php artisan tinker

# View queue status
docker-compose exec app php artisan queue:monitor
```

### Database Management

```bash
# Access MySQL CLI
docker-compose exec mysql mysql -u car_rental -p car_rental

# Backup database
docker-compose exec mysql mysqldump -u car_rental -p car_rental > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u car_rental -p car_rental < backup.sql

# View database logs
docker-compose logs -f mysql
```

### Redis Management

```bash
# Access Redis CLI
docker-compose exec redis redis-cli -a your_redis_password

# Flush all Redis data
docker-compose exec redis redis-cli -a your_redis_password FLUSHALL

# Monitor Redis
docker-compose exec redis redis-cli -a your_redis_password MONITOR

# Check Redis info
docker-compose exec redis redis-cli -a your_redis_password INFO
```

## Development vs Production

### Development Setup

For development, you can override settings:

```bash
# Create docker-compose.override.yml
cat > docker-compose.override.yml << EOF
version: '3.8'

services:
  app:
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
    volumes:
      - ./:/var/www/html
EOF

# Start with override
docker-compose up -d
```

### Production Setup

For production, ensure:

1. Use strong passwords for all services
2. Set `APP_DEBUG=false`
3. Use environment variables for sensitive data
4. Enable SSL/TLS (use reverse proxy like Traefik or nginx-proxy)
5. Set up regular backups
6. Configure monitoring

## SSL/TLS with Reverse Proxy

For production, use a reverse proxy with SSL:

### Using Traefik

```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  app:
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.car-rental.rule=Host(`yourdomain.com`)"
      - "traefik.http.routers.car-rental.entrypoints=websecure"
      - "traefik.http.routers.car-rental.tls.certresolver=letsencrypt"

networks:
  default:
    external:
      name: traefik_network
```

### Using Nginx Proxy

```bash
# Use jwilder/nginx-proxy with letsencrypt-companion
docker run -d \
  --name nginx-proxy \
  -p 80:80 -p 443:443 \
  -v /var/run/docker.sock:/tmp/docker.sock:ro \
  jwilder/nginx-proxy

# Add to docker-compose.yml
services:
  app:
    environment:
      - VIRTUAL_HOST=yourdomain.com
      - LETSENCRYPT_HOST=yourdomain.com
      - LETSENCRYPT_EMAIL=admin@yourdomain.com
```

## Scaling

### Scale Queue Workers

```bash
# Scale queue workers to 3 instances
docker-compose up -d --scale queue=3

# View scaled workers
docker-compose ps queue
```

### Resource Limits

Add resource limits to `docker-compose.yml`:

```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '1.0'
          memory: 1G
```

## Backup Strategy

### Automated Backups

Create a backup script:

```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Database backup
docker-compose exec -T mysql mysqldump \
  -u car_rental -p$DB_PASSWORD car_rental \
  | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Application files backup
tar -czf $BACKUP_DIR/app_$DATE.tar.gz \
  --exclude='node_modules' \
  --exclude='vendor' \
  ./

# Cleanup old backups (keep last 7 days)
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete
```

Schedule with cron:

```cron
0 2 * * * /path/to/backup.sh
```

## Monitoring

### Health Checks

```bash
# Check container health
docker-compose ps

# Check application health
curl http://localhost:8080/health

# Check database health
docker-compose exec mysql mysqladmin ping -h localhost

# Check Redis health
docker-compose exec redis redis-cli -a password ping
```

### Logs

```bash
# View all logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f queue
docker-compose logs -f mysql

# View last 100 lines
docker-compose logs --tail=100 app
```

### Resource Usage

```bash
# View container stats
docker stats

# View disk usage
docker system df

# Clean up unused resources
docker system prune -a
```

## Troubleshooting

### Container Won't Start

```bash
# Check logs
docker-compose logs [service-name]

# Rebuild container
docker-compose build --no-cache [service-name]
docker-compose up -d [service-name]

# Check configuration
docker-compose config
```

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Issues

```bash
# Check MySQL is running
docker-compose ps mysql

# Check MySQL logs
docker-compose logs mysql

# Test connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Queue Not Processing

```bash
# Check queue worker logs
docker-compose logs queue

# Restart queue workers
docker-compose restart queue

# Check Redis connection
docker-compose exec redis redis-cli -a password ping
```

### Out of Memory

```bash
# Check memory usage
docker stats

# Increase memory limits in docker-compose.yml
# Or increase Docker Desktop memory allocation
```

## Production Deployment Workflow

1. **Prepare**:
   ```bash
   git pull origin main
   cp .env.production.example .env
   # Edit .env with production values
   ```

2. **Build**:
   ```bash
   docker-compose build --no-cache
   ```

3. **Deploy**:
   ```bash
   docker-compose down
   docker-compose up -d
   ```

4. **Initialize**:
   ```bash
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate --force
   docker-compose exec app php artisan optimize:production
   ```

5. **Verify**:
   ```bash
   docker-compose ps
   curl http://localhost:8080
   docker-compose logs -f
   ```

## Security Best Practices

1. **Never commit `.env` file**
2. **Use strong passwords** for all services
3. **Update base images regularly**: `docker-compose pull`
4. **Scan for vulnerabilities**: `docker scan car-rental_app`
5. **Use secrets management** for sensitive data
6. **Limit container privileges**
7. **Use read-only file systems** where possible
8. **Enable security scanning** in CI/CD pipeline

## Performance Optimization

1. **Enable OPcache** (already configured in `docker/php/opcache.ini`)
2. **Use Redis** for cache and sessions
3. **Optimize images**: Use multi-stage builds
4. **Use volume caching** for better performance
5. **Tune PHP-FPM** pool settings
6. **Configure Nginx** worker processes
7. **Use CDN** for static assets

## Useful Links

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.configuration.php)

---

**Last Updated**: 2025-11-22

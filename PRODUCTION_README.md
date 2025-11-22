# Production Deployment - Car Rental System

Complete guide to deploying the Car Rental System to production.

## Overview

This project includes comprehensive production deployment resources to help you deploy the application securely and efficiently.

## Documentation Files

### Core Documentation

- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Complete deployment guide with step-by-step instructions
- **[PRODUCTION_QUICK_REFERENCE.md](PRODUCTION_QUICK_REFERENCE.md)** - Quick command reference for common operations
- **[PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)** - Comprehensive checklist for deployment
- **[DOCKER_README.md](DOCKER_README.md)** - Docker deployment guide

### Configuration Files

- **[.env.production.example](.env.production.example)** - Production environment template
- **[docker-compose.yml](docker-compose.yml)** - Docker orchestration configuration
- **[Dockerfile](Dockerfile)** - Container build configuration

### Deployment Scripts

- **[deploy.sh](deploy.sh)** - Automated deployment script
- **[optimize-production.sh](optimize-production.sh)** - Production optimization script

### Artisan Commands

- `php artisan optimize:production` - Optimize application for production

## Quick Start Options

### Option 1: Traditional Server Deployment

**Best for**: VPS, dedicated servers, traditional hosting

1. Follow **[DEPLOYMENT.md](DEPLOYMENT.md)**
2. Use deployment script: `./deploy.sh`
3. Run optimization: `php artisan optimize:production`

**Server Requirements**:
- PHP 8.2+
- MySQL 8.0+
- Redis 6.0+
- Nginx/Apache
- Node.js 18+
- Supervisor (for queues)

### Option 2: Docker Deployment

**Best for**: Containerized environments, easy scaling, consistent deployments

1. Follow **[DOCKER_README.md](DOCKER_README.md)**
2. Run: `docker-compose up -d`
3. Initialize: `docker-compose exec app php artisan optimize:production`

**Requirements**:
- Docker Engine 20.10+
- Docker Compose 2.0+
- 4GB RAM minimum

## Deployment Process

### Step 1: Pre-Deployment

- [ ] Review **[PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)**
- [ ] Configure environment (`.env` from `.env.production.example`)
- [ ] Set up server/Docker environment
- [ ] Configure database
- [ ] Set up Redis
- [ ] Configure mail service
- [ ] Configure SMS service (Macrokiosk)

### Step 2: Deploy

**Traditional Server**:
```bash
./deploy.sh
```

**Docker**:
```bash
docker-compose up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan optimize:production
```

### Step 3: Post-Deployment

- [ ] Run optimization: `php artisan optimize:production`
- [ ] Verify application is accessible
- [ ] Test critical functionality
- [ ] Set up monitoring
- [ ] Configure backups
- [ ] Review logs

## Key Features

### Security

- ✅ SSL/TLS certificate configuration
- ✅ Security headers (nginx configuration included)
- ✅ Environment variable protection
- ✅ Database credential encryption
- ✅ CSRF protection (Laravel default)
- ✅ Rate limiting
- ✅ Secure session management

### Performance

- ✅ OPcache configuration
- ✅ Redis caching (config, session, queue)
- ✅ Route caching
- ✅ View caching
- ✅ Configuration caching
- ✅ Gzip compression
- ✅ Static asset caching
- ✅ Optimized autoloader

### Scalability

- ✅ Queue workers (background jobs)
- ✅ Task scheduler
- ✅ Database connection pooling
- ✅ Docker scaling support
- ✅ CDN ready
- ✅ Load balancer ready

## Production Environment Configuration

### Critical Settings

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=car_rental_production
DB_USERNAME=car_rental_user
DB_PASSWORD=strong_password_here

# Cache & Queue (Redis)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# File Storage (S3)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_BUCKET=your_bucket

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@yourdomain.com

# SMS (Macrokiosk)
SMS_USERNAME=your_username
SMS_PASSWORD=your_password
SMS_SERVICE_ID=your_service_id
SMS_API_KEY=your_api_key
SMS_USE_JWT=true
```

## Common Tasks

### Deployment Updates

```bash
# Pull latest code
git pull origin main

# Run deployment script
./deploy.sh

# Or with Docker
docker-compose pull
docker-compose up -d --build
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan optimize:production
```

### Cache Management

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild all caches
php artisan optimize:production
```

### Queue Management

```bash
# Restart queue workers
php artisan queue:restart

# Monitor queues
php artisan queue:monitor

# View failed jobs
php artisan queue:failed
```

### Database Operations

```bash
# Run migrations
php artisan migrate --force

# Backup database
mysqldump -u user -p database | gzip > backup.sql.gz

# Restore database
gunzip < backup.sql.gz | mysql -u user -p database
```

## Monitoring & Maintenance

### Log Files

- **Laravel**: `storage/logs/laravel.log`
- **Nginx**: `/var/log/nginx/car-rental-*.log`
- **PHP-FPM**: `/var/log/php8.2-fpm.log`
- **Supervisor**: `/var/log/supervisor/supervisord.log`

### Health Checks

```bash
# Application info
php artisan about

# Database connection
php artisan db:show

# Queue monitoring
php artisan queue:monitor

# Check Redis
redis-cli ping
```

### Performance Monitoring

- Enable Laravel Telescope (dev only)
- Use Laravel Horizon for queue monitoring
- Configure application performance monitoring (APM)
- Set up server monitoring (CPU, RAM, Disk)
- Monitor database performance

## Backup Strategy

### Database Backups

```bash
# Daily automated backup
0 2 * * * /path/to/backup-script.sh

# Backup script
mysqldump -u user -p'password' database | gzip > backup-$(date +\%Y\%m\%d).sql.gz
```

### Application Backups

```bash
# Weekly full backup
tar -czf backup-$(date +\%Y\%m\%d).tar.gz \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='storage/logs/*' \
  /var/www/car-rental
```

### Backup Retention

- Daily backups: Keep for 7 days
- Weekly backups: Keep for 4 weeks
- Monthly backups: Keep for 12 months

## Security Best Practices

1. ✅ **Environment**: Set `APP_DEBUG=false` in production
2. ✅ **SSL/TLS**: Always use HTTPS in production
3. ✅ **Passwords**: Use strong, unique passwords for all services
4. ✅ **Updates**: Keep dependencies updated (`composer update`, `npm update`)
5. ✅ **Backups**: Regular automated backups with tested restoration
6. ✅ **Firewall**: Configure firewall (allow only 80, 443, 22)
7. ✅ **Monitoring**: Set up error monitoring (Sentry, Bugsnag, etc.)
8. ✅ **Logs**: Regularly review application and server logs
9. ✅ **Permissions**: Proper file permissions (775 for storage)
10. ✅ **Secrets**: Never commit `.env` or credentials to git

## Performance Optimization

### Application Level

- ✅ Enable all Laravel caches (config, route, view)
- ✅ Use Redis for cache, sessions, and queues
- ✅ Optimize Composer autoloader
- ✅ Enable OPcache
- ✅ Use Laravel Octane (optional, for high traffic)

### Database Level

- ✅ Add proper indexes to frequently queried columns
- ✅ Optimize slow queries
- ✅ Use database connection pooling
- ✅ Enable query caching where appropriate

### Server Level

- ✅ Use HTTP/2
- ✅ Enable Gzip compression
- ✅ Configure browser caching for static assets
- ✅ Use a CDN for static assets
- ✅ Optimize web server (Nginx worker processes)
- ✅ Tune PHP-FPM pool settings

## Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check `storage/logs/laravel.log`
- Verify file permissions (`chmod -R 775 storage bootstrap/cache`)
- Clear and rebuild caches

**Queue Not Processing**
- Check supervisor status
- Restart queue workers
- Verify Redis connection

**Database Connection Failed**
- Verify credentials in `.env`
- Check MySQL is running
- Test connection with MySQL client

**Assets Not Loading**
- Rebuild assets: `npm run build`
- Clear view cache: `php artisan view:clear`
- Check nginx configuration

For detailed troubleshooting, see **[PRODUCTION_QUICK_REFERENCE.md](PRODUCTION_QUICK_REFERENCE.md#troubleshooting)**

## Support & Resources

### Documentation

- Laravel Documentation: https://laravel.com/docs
- Deployment Guide: [DEPLOYMENT.md](DEPLOYMENT.md)
- Docker Guide: [DOCKER_README.md](DOCKER_README.md)
- Quick Reference: [PRODUCTION_QUICK_REFERENCE.md](PRODUCTION_QUICK_REFERENCE.md)
- Checklist: [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)

### External Resources

- PHP Best Practices: https://www.php-fig.org/
- Nginx Configuration: https://nginx.org/en/docs/
- MySQL Optimization: https://dev.mysql.com/doc/
- Redis Documentation: https://redis.io/documentation
- Macrokiosk SMS: https://www.macrokiosk.com

## Deployment Workflow Diagram

```
┌─────────────────────────────────────────────────────┐
│                  Pre-Deployment                     │
│  - Review checklist                                 │
│  - Configure environment (.env)                     │
│  - Set up infrastructure                            │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│                    Deployment                       │
│  Option 1: ./deploy.sh (Traditional)                │
│  Option 2: docker-compose up -d (Docker)            │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│                  Optimization                       │
│  php artisan optimize:production                    │
│  - Clear caches                                     │
│  - Rebuild caches (config, routes, views)           │
│  - Optimize autoloader                              │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│                 Post-Deployment                     │
│  - Verify application                               │
│  - Test functionality                               │
│  - Set up monitoring                                │
│  - Configure backups                                │
└─────────────────────────────────────────────────────┘
```

## Project Structure

```
car-rental/
├── .env.production.example      # Production environment template
├── deploy.sh                    # Deployment automation script
├── optimize-production.sh       # Production optimization script
├── docker-compose.yml           # Docker orchestration
├── Dockerfile                   # Container build file
├── DEPLOYMENT.md                # Full deployment guide
├── PRODUCTION_README.md         # This file
├── PRODUCTION_QUICK_REFERENCE.md # Quick command reference
├── PRODUCTION_CHECKLIST.md      # Deployment checklist
├── DOCKER_README.md             # Docker deployment guide
├── app/
│   └── Console/Commands/
│       └── OptimizeProduction.php # Production optimization command
├── docker/                      # Docker configuration files
│   ├── nginx/                   # Nginx configuration
│   ├── php/                     # PHP configuration
│   ├── supervisor/              # Supervisor configuration
│   └── mysql/                   # MySQL configuration
└── ...
```

## Next Steps

1. **Read** the full deployment guide: [DEPLOYMENT.md](DEPLOYMENT.md)
2. **Choose** your deployment method (Traditional or Docker)
3. **Follow** the deployment checklist: [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)
4. **Deploy** using the appropriate method
5. **Monitor** and maintain using the quick reference guide

## License

This deployment configuration is part of the Car Rental System project.

---

**Version**: 1.0.0
**Last Updated**: 2025-11-22
**Maintained By**: Development Team

For questions or issues, please refer to the documentation or contact the development team.

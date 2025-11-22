# Production Quick Reference

Quick reference guide for common production operations.

## Initial Setup

```bash
# 1. Clone and navigate to project
git clone <repository-url> car-rental
cd car-rental

# 2. Copy environment file
cp .env.production.example .env

# 3. Edit .env with production values
nano .env

# 4. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# 5. Generate app key
php artisan key:generate

# 6. Run migrations
php artisan migrate --force

# 7. Optimize for production
php artisan optimize:production
# OR use the bash script
chmod +x optimize-production.sh
./optimize-production.sh

# 8. Set permissions
chmod -R 775 storage bootstrap/cache
```

## Deployment

### Automated Deployment

```bash
chmod +x deploy.sh
./deploy.sh
```

### Quick Manual Deployment

```bash
# Enable maintenance mode
php artisan down --retry=60

# Pull latest changes
git pull origin main

# Update dependencies and build
composer install --no-dev --optimize-autoloader
npm ci --production && npm run build

# Optimize application
php artisan migrate --force
php artisan optimize:production

# Disable maintenance mode
php artisan up
```

## Common Commands

### Cache Management

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild all caches
php artisan optimize:production

# Clear specific cache
php artisan cache:forget key_name
```

### Queue Management

```bash
# Restart queue workers
php artisan queue:restart

# Monitor queue
php artisan queue:monitor

# Manually work queue
php artisan queue:work --tries=3

# Check failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry <job-id>

# Retry all failed jobs
php artisan queue:retry all
```

### Database

```bash
# Run migrations
php artisan migrate --force

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Fresh database (DANGER: drops all tables)
php artisan migrate:fresh --force
```

### Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View last 100 lines
tail -n 100 storage/logs/laravel.log

# Search logs for errors
grep "ERROR" storage/logs/laravel.log

# Clear old logs (manually)
> storage/logs/laravel.log
```

### Supervisor (Queue Workers)

```bash
# Check status
sudo supervisorctl status

# Start workers
sudo supervisorctl start car-rental-worker:*

# Stop workers
sudo supervisorctl stop car-rental-worker:*

# Restart workers
sudo supervisorctl restart car-rental-worker:*

# Reload configuration
sudo supervisorctl reread
sudo supervisorctl update
```

### Web Server

```bash
# Nginx
sudo systemctl status nginx
sudo systemctl restart nginx
sudo systemctl reload nginx
sudo nginx -t  # Test configuration

# PHP-FPM
sudo systemctl status php8.2-fpm
sudo systemctl restart php8.2-fpm
```

### Performance

```bash
# Show application info
php artisan about

# Run in debug mode temporarily
php artisan serve --host=0.0.0.0 --port=8000

# Check route list
php artisan route:list

# Database query monitoring
php artisan db:monitor
```

## Troubleshooting

### 500 Error

```bash
# Check logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Clear and rebuild caches
php artisan cache:clear
php artisan config:clear
php artisan optimize:production

# Check permissions
chmod -R 775 storage bootstrap/cache
```

### Queue Not Processing

```bash
# Check supervisor
sudo supervisorctl status

# Restart workers
php artisan queue:restart
sudo supervisorctl restart car-rental-worker:*

# Check Redis
redis-cli ping
redis-cli info
```

### Assets Not Loading

```bash
# Rebuild assets
npm run build

# Clear cache
php artisan cache:clear
php artisan view:clear

# Check storage link
php artisan storage:link
```

### Database Connection Issues

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql

# Check database credentials in .env
cat .env | grep DB_
```

## Security Commands

```bash
# Generate new app key
php artisan key:generate

# Clear config cache (after .env changes)
php artisan config:clear

# Check for security updates
composer outdated

# Update dependencies
composer update --with-dependencies
```

## Backup

```bash
# Database backup
mysqldump -u user -p database_name | gzip > backup-$(date +%Y%m%d).sql.gz

# Application backup
tar -czf car-rental-backup-$(date +%Y%m%d).tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs/*' \
    /var/www/car-rental

# Restore database
gunzip < backup-20250122.sql.gz | mysql -u user -p database_name
```

## Monitoring

```bash
# Check disk space
df -h

# Check memory usage
free -h

# Check CPU usage
top

# Check running processes
ps aux | grep php
ps aux | grep nginx

# Check open files
lsof -i :80
lsof -i :443

# Check network connections
netstat -tulpn
```

## SSL Certificate (Let's Encrypt)

```bash
# Renew certificate
sudo certbot renew

# Test renewal
sudo certbot renew --dry-run

# Check certificate expiry
sudo certbot certificates
```

## Environment Variables

Critical production environment variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_DATABASE=car_rental_production

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
FILESYSTEM_DISK=s3
```

## Maintenance Mode

```bash
# Enable maintenance mode
php artisan down
php artisan down --retry=60  # Retry after 60 seconds
php artisan down --secret=secret-token  # Bypass with token

# Disable maintenance mode
php artisan up

# Check if in maintenance mode
php artisan about
```

## Health Checks

```bash
# Quick health check
php artisan about

# Check environment
php artisan env

# Check database connection
php artisan db:show

# Check queue connection
php artisan queue:monitor

# Verify storage is writable
touch storage/logs/test.log && rm storage/logs/test.log
```

## Performance Monitoring

```bash
# Enable query logging temporarily
php artisan tinker
>>> DB::enableQueryLog();
>>> // Run some code
>>> DB::getQueryLog();

# Check Redis
redis-cli
> INFO stats
> DBSIZE
> MEMORY USAGE <key>

# Check MySQL processes
mysql -u root -p
> SHOW PROCESSLIST;
> SHOW STATUS LIKE 'Threads_connected';
```

## Emergency Procedures

### Application Down

1. Check logs: `tail -f storage/logs/laravel.log`
2. Check web server: `sudo systemctl status nginx`
3. Check PHP-FPM: `sudo systemctl status php8.2-fpm`
4. Clear caches: `php artisan cache:clear && php artisan config:clear`
5. Restart services: `sudo systemctl restart php8.2-fpm nginx`

### Database Issues

1. Check connection: `php artisan tinker` → `DB::connection()->getPdo()`
2. Check MySQL: `sudo systemctl status mysql`
3. Check credentials in `.env`
4. Check MySQL logs: `sudo tail /var/log/mysql/error.log`

### High Load

1. Check processes: `top`
2. Check queue workers: `ps aux | grep "queue:work"`
3. Check logs for errors
4. Consider enabling maintenance mode
5. Scale horizontally if needed

## Contact Information

- **Documentation**: See DEPLOYMENT.md for full deployment guide
- **Logs Location**: `storage/logs/laravel.log`
- **Config Location**: `.env`
- **Web Server Config**: `/etc/nginx/sites-available/car-rental`

---

**Last Updated**: 2025-11-22

# Production Deployment Guide

This guide covers deploying the Car Rental System to a production environment.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Requirements](#server-requirements)
3. [Initial Setup](#initial-setup)
4. [Configuration](#configuration)
5. [Deployment Process](#deployment-process)
6. [Post-Deployment](#post-deployment)
7. [Monitoring & Maintenance](#monitoring--maintenance)
8. [Troubleshooting](#troubleshooting)

## Prerequisites

### Required Software

- PHP 8.2 or higher
- MySQL 8.0 or higher (or MariaDB 10.3+)
- Redis 6.0+ (for caching and queues)
- Node.js 18+ and NPM
- Composer 2.x
- Web server (Nginx recommended, Apache also supported)
- SSL certificate (Let's Encrypt recommended)

### Required PHP Extensions

```bash
php -m | grep -E 'bcmath|ctype|fileinfo|json|mbstring|openssl|pdo_mysql|tokenizer|xml|redis|gd|curl|zip'
```

All of the above extensions must be installed and enabled.

## Server Requirements

### Recommended Specifications

- **CPU**: 2+ cores
- **RAM**: 4GB minimum (8GB recommended)
- **Storage**: 20GB+ SSD
- **Bandwidth**: Unmetered or 1TB+

### Operating System

- Ubuntu 22.04 LTS (recommended)
- Debian 11+
- CentOS 8+ / Rocky Linux 8+

## Initial Setup

### 1. Clone Repository

```bash
cd /var/www
git clone <your-repository-url> car-rental
cd car-rental
```

### 2. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/car-rental
sudo chmod -R 755 /var/www/car-rental
sudo chmod -R 775 /var/www/car-rental/storage
sudo chmod -R 775 /var/www/car-rental/bootstrap/cache
```

### 3. Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm ci --production

# Build frontend assets
npm run build
```

## Configuration

### 1. Environment Configuration

Copy the production environment template:

```bash
cp .env.production .env
```

Edit `.env` and configure the following:

#### Application Settings

```env
APP_NAME="Your Car Rental Company"
APP_ENV=production
APP_KEY=  # Will be generated in next step
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

#### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=car_rental_production
DB_USERNAME=car_rental_user
DB_PASSWORD=your_secure_password_here
```

#### Redis

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379
```

#### Mail

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### SMS (Macrokiosk)

```env
SMS_USERNAME=your_macrokiosk_username
SMS_PASSWORD=your_macrokiosk_password
SMS_SERVICE_ID=your_service_id
SMS_API_KEY=your_api_key
SMS_USE_JWT=true
```

#### AWS S3 (for file storage)

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

### 2. Generate Application Key

```bash
php artisan key:generate
```

### 3. Database Setup

Create the production database:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE car_rental_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'car_rental_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON car_rental_production.* TO 'car_rental_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Run migrations:

```bash
php artisan migrate --force
```

### 4. Seed Initial Data (Optional)

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

## Deployment Process

### Automated Deployment

Use the provided deployment script:

```bash
chmod +x deploy.sh
./deploy.sh
```

### Manual Deployment

If you prefer manual deployment:

```bash
# 1. Enable maintenance mode
php artisan down --retry=60

# 2. Pull latest changes
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# 4. Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 5. Run migrations
php artisan migrate --force

# 6. Clear caches
php artisan cache:clear

# 7. Restart queue workers
php artisan queue:restart

# 8. Fix permissions
chmod -R 775 storage bootstrap/cache

# 9. Disable maintenance mode
php artisan up
```

## Web Server Configuration

### Nginx Configuration

Create `/etc/nginx/sites-available/car-rental`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/car-rental/public;

    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Logging
    access_log /var/log/nginx/car-rental-access.log;
    error_log /var/log/nginx/car-rental-error.log;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/car-rental /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

## Post-Deployment

### 1. Queue Workers

Create a supervisor configuration for queue workers:

`/etc/supervisor/conf.d/car-rental-worker.conf`:

```ini
[program:car-rental-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/car-rental/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasflimit=3600
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/car-rental/storage/logs/worker.log
stopwaitsecs=3600
```

Start the workers:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start car-rental-worker:*
```

### 2. Scheduler (Cron)

Add to crontab (`sudo crontab -e -u www-data`):

```cron
* * * * * cd /var/www/car-rental && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Verify Installation

Run the optimization script:

```bash
php artisan optimize:production
```

Check system health:

```bash
php artisan about
```

## Monitoring & Maintenance

### Log Monitoring

```bash
# Monitor application logs
tail -f storage/logs/laravel.log

# Monitor queue logs
tail -f storage/logs/worker.log

# Monitor nginx logs
tail -f /var/log/nginx/car-rental-error.log
```

### Database Backups

Set up automated backups using a cron job:

```bash
0 2 * * * mysqldump -u car_rental_user -p'password' car_rental_production | gzip > /backups/car-rental-$(date +\%Y\%m\%d).sql.gz
```

### Application Updates

When deploying updates:

```bash
./deploy.sh
```

### Cache Clearing

If you need to clear all caches:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Troubleshooting

### Issue: White Screen / 500 Error

1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Check nginx error logs: `tail -f /var/log/nginx/car-rental-error.log`
3. Check PHP-FPM logs: `tail -f /var/log/php8.2-fpm.log`
4. Ensure proper permissions: `sudo chmod -R 775 storage bootstrap/cache`

### Issue: Queue Jobs Not Processing

1. Check supervisor status: `sudo supervisorctl status`
2. Restart workers: `sudo supervisorctl restart car-rental-worker:*`
3. Check Redis connection: `redis-cli ping`

### Issue: Assets Not Loading

1. Rebuild assets: `npm run build`
2. Clear cache: `php artisan cache:clear`
3. Check nginx configuration for static file serving

### Issue: SMS Not Sending

1. Check SMS logs: `tail -f storage/logs/laravel.log | grep SMS`
2. Verify Macrokiosk credentials in `.env`
3. Check queue workers are running
4. Test SMS service: `php artisan sms:test`

### Issue: Database Connection Failed

1. Verify database credentials in `.env`
2. Check MySQL is running: `sudo systemctl status mysql`
3. Test connection: `mysql -u car_rental_user -p car_rental_production`

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Strong database passwords
- [ ] Redis password set (if exposed)
- [ ] Firewall configured (UFW/iptables)
- [ ] SSL certificate installed and auto-renewal configured
- [ ] Security headers configured in nginx
- [ ] File permissions properly set (775 for storage, 755 for others)
- [ ] Unnecessary ports closed
- [ ] Regular backups configured
- [ ] Monitoring and alerting set up
- [ ] Rate limiting enabled
- [ ] CSRF protection enabled (default in Laravel)
- [ ] SQL injection protection (use Eloquent/Query Builder)

## Performance Optimization

1. **Enable OPcache**: Edit `/etc/php/8.2/fpm/php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=256
   opcache.max_accelerated_files=20000
   opcache.validate_timestamps=0
   ```

2. **Use Redis for sessions and cache**:
   ```env
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   ```

3. **Enable Laravel Octane** (optional, for high-traffic):
   ```bash
   composer require laravel/octane
   php artisan octane:install --server=swoole
   ```

4. **Database Indexing**: Ensure proper indexes on frequently queried columns

5. **CDN**: Use a CDN for static assets (CloudFlare, AWS CloudFront, etc.)

## Support

For deployment issues or questions:
- Check Laravel documentation: https://laravel.com/docs
- Review application logs
- Contact development team

---

**Last Updated**: 2025-11-22

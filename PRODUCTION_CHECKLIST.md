# Production Deployment Checklist

Use this checklist before, during, and after deploying to production.

## Pre-Deployment Checklist

### Environment Configuration

- [ ] `.env` file created from `.env.production.example`
- [ ] `APP_ENV=production` set
- [ ] `APP_DEBUG=false` set
- [ ] `APP_KEY` generated (run `php artisan key:generate`)
- [ ] `APP_URL` set to production domain (https://yourdomain.com)
- [ ] `APP_NAME` set to your application name

### Database Configuration

- [ ] Production database created
- [ ] Database user created with appropriate permissions
- [ ] `DB_*` credentials configured in `.env`
- [ ] Database connection tested
- [ ] Migrations reviewed and tested in staging
- [ ] Database backup strategy in place

### Cache & Session

- [ ] Redis installed and running
- [ ] `CACHE_STORE=redis` configured
- [ ] `SESSION_DRIVER=redis` configured
- [ ] `QUEUE_CONNECTION=redis` configured
- [ ] Redis password set (if exposed externally)
- [ ] Redis connection tested

### Mail Configuration

- [ ] Mail provider configured (SMTP credentials)
- [ ] `MAIL_FROM_ADDRESS` set
- [ ] `MAIL_FROM_NAME` set
- [ ] Test email sent successfully
- [ ] Mail queue configured properly

### SMS Configuration (Macrokiosk)

- [ ] Macrokiosk account credentials obtained
- [ ] `SMS_USERNAME` configured
- [ ] `SMS_PASSWORD` configured
- [ ] `SMS_SERVICE_ID` configured
- [ ] `SMS_API_KEY` configured (if using JWT)
- [ ] `SMS_USE_JWT=true` (recommended for production)
- [ ] Test SMS sent successfully
- [ ] SMS webhook endpoint accessible

### File Storage

- [ ] AWS S3 bucket created
- [ ] IAM user created with S3 permissions
- [ ] `AWS_*` credentials configured
- [ ] `FILESYSTEM_DISK=s3` set
- [ ] File upload tested
- [ ] Bucket CORS configured if needed
- [ ] Bucket policy reviewed

### Security

- [ ] SSL certificate installed
- [ ] SSL certificate auto-renewal configured
- [ ] Strong passwords used for all services
- [ ] Database credentials secured
- [ ] API keys and secrets secured
- [ ] `config:cache` will be run (closes .env access)
- [ ] CORS policy configured
- [ ] Rate limiting enabled
- [ ] Security headers configured in web server
- [ ] Firewall rules configured (UFW/iptables)
- [ ] SSH key-based authentication enabled
- [ ] Root SSH login disabled
- [ ] Fail2ban installed and configured

## Server Setup Checklist

### Server Requirements

- [ ] PHP 8.2+ installed
- [ ] MySQL 8.0+ installed
- [ ] Redis 6.0+ installed
- [ ] Node.js 18+ installed
- [ ] Composer 2.x installed
- [ ] Nginx/Apache installed and configured
- [ ] Supervisor installed (for queue workers)
- [ ] All required PHP extensions installed

### PHP Extensions

- [ ] bcmath
- [ ] ctype
- [ ] fileinfo
- [ ] json
- [ ] mbstring
- [ ] openssl
- [ ] pdo_mysql
- [ ] tokenizer
- [ ] xml
- [ ] redis
- [ ] gd
- [ ] curl
- [ ] zip

### PHP Configuration

- [ ] OPcache enabled and configured
- [ ] `memory_limit` appropriate (256M+)
- [ ] `upload_max_filesize` appropriate
- [ ] `post_max_size` appropriate
- [ ] `max_execution_time` appropriate
- [ ] `max_input_time` appropriate

### Web Server Configuration

- [ ] Virtual host/server block configured
- [ ] Document root points to `/public`
- [ ] SSL configured
- [ ] Security headers configured
- [ ] Gzip compression enabled
- [ ] Static file caching configured
- [ ] HTTP/2 enabled
- [ ] Rate limiting configured

### File Permissions

- [ ] Application owned by web server user (www-data)
- [ ] `storage/` directory: 775 permissions
- [ ] `bootstrap/cache/` directory: 775 permissions
- [ ] All other files: 755 permissions
- [ ] `.env` file: 600 permissions

## Deployment Checklist

### Code Deployment

- [ ] Latest code pulled from repository
- [ ] Git branch verified (main/production)
- [ ] Composer dependencies installed (`--no-dev --optimize-autoloader`)
- [ ] NPM dependencies installed (`npm ci --production`)
- [ ] Frontend assets built (`npm run build`)

### Database

- [ ] Backup created before migration
- [ ] Migrations reviewed
- [ ] Migrations run successfully
- [ ] Seeders run (if needed)
- [ ] Database verified

### Optimization

- [ ] Configuration cached (`php artisan config:cache`)
- [ ] Routes cached (`php artisan route:cache`)
- [ ] Views cached (`php artisan view:cache`)
- [ ] Events cached (`php artisan event:cache`)
- [ ] Application optimized (`php artisan optimize`)
- [ ] Storage link created (`php artisan storage:link`)

### Queue Workers

- [ ] Supervisor configuration created
- [ ] Queue workers started
- [ ] Queue workers verified running
- [ ] Failed jobs queue checked
- [ ] Queue worker logs checked

### Scheduler

- [ ] Cron job configured for Laravel scheduler
- [ ] Cron job tested
- [ ] Scheduled tasks verified

## Post-Deployment Checklist

### Verification

- [ ] Application loads successfully (homepage)
- [ ] SSL certificate valid and working
- [ ] All major pages load correctly
- [ ] Forms submit successfully
- [ ] File uploads work
- [ ] Database queries work
- [ ] Cache works (Redis)
- [ ] Sessions work
- [ ] Authentication works
- [ ] API endpoints respond correctly
- [ ] Email sending works
- [ ] SMS sending works
- [ ] Queue jobs process
- [ ] Scheduled tasks run

### Testing Critical Functionality

- [ ] User registration works
- [ ] User login works
- [ ] Password reset works
- [ ] Booking creation works
- [ ] Payment processing works
- [ ] SMS notifications sent
- [ ] Email notifications sent
- [ ] Admin panel accessible
- [ ] Reports generate correctly
- [ ] Export functionality works

### Monitoring Setup

- [ ] Application monitoring configured (optional: Sentry, Bugsnag)
- [ ] Server monitoring configured (optional: New Relic, DataDog)
- [ ] Uptime monitoring configured (optional: Pingdom, UptimeRobot)
- [ ] Log aggregation configured (optional: Papertrail, Loggly)
- [ ] Error tracking tested
- [ ] Alerts configured for critical errors
- [ ] Performance monitoring enabled

### Logs

- [ ] Application logs checked for errors
- [ ] Web server logs checked for errors
- [ ] PHP-FPM logs checked for errors
- [ ] Queue worker logs checked for errors
- [ ] MySQL slow query log checked
- [ ] No critical errors present

### Performance

- [ ] Page load times acceptable
- [ ] Database queries optimized
- [ ] N+1 queries resolved
- [ ] Asset loading times acceptable
- [ ] API response times acceptable
- [ ] Redis performance checked
- [ ] Server resources (CPU, RAM, Disk) checked

### Backup Strategy

- [ ] Database backup configured
- [ ] Database backup tested
- [ ] Backup restoration tested
- [ ] Backup retention policy defined
- [ ] Off-site backups configured
- [ ] Application files backup configured

### Documentation

- [ ] Deployment guide updated
- [ ] Production credentials documented (securely)
- [ ] Access credentials documented (securely)
- [ ] Emergency contacts documented
- [ ] Runbook created for common issues
- [ ] Team trained on deployment process

## Ongoing Maintenance Checklist

### Daily

- [ ] Check application logs for errors
- [ ] Monitor server resource usage
- [ ] Verify queue workers running
- [ ] Check backup completion

### Weekly

- [ ] Review failed jobs
- [ ] Check disk space
- [ ] Review slow query log
- [ ] Update dependencies (security patches)
- [ ] Review application metrics

### Monthly

- [ ] Test backup restoration
- [ ] Review and rotate logs
- [ ] Update OS packages
- [ ] Review SSL certificate expiry
- [ ] Performance audit
- [ ] Security audit
- [ ] Review and update documentation

### Quarterly

- [ ] Major dependency updates
- [ ] Security penetration testing
- [ ] Disaster recovery drill
- [ ] Review and update monitoring
- [ ] Capacity planning review

## Emergency Procedures

### Application Down

1. [ ] Check application logs
2. [ ] Check web server status
3. [ ] Check PHP-FPM status
4. [ ] Check database status
5. [ ] Check Redis status
6. [ ] Clear caches
7. [ ] Restart services
8. [ ] Enable maintenance mode if needed

### Database Issues

1. [ ] Check database connection
2. [ ] Check database status
3. [ ] Check credentials
4. [ ] Review error logs
5. [ ] Restore from backup if needed

### High Server Load

1. [ ] Check running processes
2. [ ] Check queue workers
3. [ ] Check for memory leaks
4. [ ] Enable maintenance mode
5. [ ] Scale resources if needed

## Rollback Procedure

- [ ] Backup current state
- [ ] Enable maintenance mode
- [ ] Restore previous code version
- [ ] Run migrations down (if needed)
- [ ] Restore database backup (if needed)
- [ ] Clear caches
- [ ] Verify application works
- [ ] Disable maintenance mode

## Sign-off

Deployment completed by: ________________
Date: ________________
Environment: ________________
Version/Tag: ________________

Verified by: ________________
Date: ________________

---

**Notes:**

Use this checklist for every production deployment. Keep it updated with your specific requirements.

For detailed instructions, see:
- DEPLOYMENT.md - Full deployment guide
- PRODUCTION_QUICK_REFERENCE.md - Quick command reference

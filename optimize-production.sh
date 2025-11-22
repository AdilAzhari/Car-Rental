#!/bin/bash

###############################################################################
# Car Rental System - Production Optimization Script
# Run this script after deployment to optimize the application for production
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Production Optimization${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Function to print step
print_step() {
    echo -e "${YELLOW}[OPTIMIZING]${NC} $1"
}

# Function to print success
print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Function to print info
print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Function to print error
print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if .env exists and is production
if [ ! -f ".env" ]; then
    print_error ".env file not found!"
    exit 1
fi

APP_ENV=$(grep "^APP_ENV=" .env | cut -d '=' -f2)
if [ "$APP_ENV" != "production" ]; then
    print_error "APP_ENV is not set to 'production' in .env file"
    echo "Current APP_ENV: $APP_ENV"
    echo "Please update your .env file before running optimization"
    exit 1
fi

print_info "Environment: $APP_ENV"
echo ""

# Clear all caches first
print_step "Clearing all existing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
print_success "All caches cleared"

# Optimize Composer autoloader
print_step "Optimizing Composer autoloader..."
composer dump-autoload --optimize --no-dev
print_success "Composer autoloader optimized"

# Cache configuration
print_step "Caching configuration files..."
php artisan config:cache
print_success "Configuration cached"

# Cache routes
print_step "Caching routes..."
php artisan route:cache
print_success "Routes cached"

# Cache views
print_step "Caching views..."
php artisan view:cache
print_success "Views cached"

# Cache events
print_step "Caching events..."
php artisan event:cache
print_success "Events cached"

# Optimize application
print_step "Running Laravel optimize command..."
php artisan optimize
print_success "Application optimized"

# Storage link
print_step "Creating storage link..."
php artisan storage:link || print_info "Storage link already exists"

# Check file permissions
print_step "Checking file permissions..."
if [ -d "storage" ]; then
    chmod -R 775 storage
    print_success "Storage permissions set to 775"
fi

if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
    print_success "Bootstrap cache permissions set to 775"
fi

# Check queue workers
print_step "Restarting queue workers..."
php artisan queue:restart
print_success "Queue workers restarted"

# Display optimization summary
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Optimization Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Run Laravel about command to show configuration
echo -e "${BLUE}Application Information:${NC}"
php artisan about

echo ""
echo -e "${YELLOW}Optimization Summary:${NC}"
echo "✓ Composer autoloader optimized"
echo "✓ Configuration cached"
echo "✓ Routes cached"
echo "✓ Views cached"
echo "✓ Events cached"
echo "✓ Application optimized"
echo "✓ Storage linked"
echo "✓ Permissions verified"
echo "✓ Queue workers restarted"

echo ""
echo -e "${YELLOW}Recommendations:${NC}"
echo "1. Monitor application logs: tail -f storage/logs/laravel.log"
echo "2. Monitor queue workers: sudo supervisorctl status"
echo "3. Check Redis status: redis-cli ping"
echo "4. Monitor database connections: SHOW PROCESSLIST (in MySQL)"
echo "5. Set up monitoring tools (Sentry, Bugsnag, etc.)"

# Performance tips
echo ""
echo -e "${BLUE}Performance Tips:${NC}"
echo "• Enable OPcache in php.ini (opcache.enable=1)"
echo "• Use Redis for cache, session, and queue drivers"
echo "• Consider Laravel Octane for high-traffic applications"
echo "• Use a CDN for static assets"
echo "• Enable HTTP/2 in your web server"
echo "• Set up database connection pooling"
echo "• Enable gzip compression in nginx/apache"

# Security reminders
echo ""
echo -e "${RED}Security Checklist:${NC}"
echo "□ APP_DEBUG is set to false"
echo "□ Strong APP_KEY is generated"
echo "□ Database credentials are secure"
echo "□ SSL certificate is installed"
echo "□ Security headers are configured"
echo "□ File permissions are correct"
echo "□ Firewall is configured"
echo "□ Backups are scheduled"

echo ""
print_success "Production optimization completed successfully!"
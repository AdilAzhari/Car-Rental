#!/bin/bash

###############################################################################
# Car Rental System - Production Deployment Script
# This script automates the deployment process for production environments
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="${APP_DIR:-$(pwd)}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Car Rental System - Deployment${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Function to print step
print_step() {
    echo -e "${YELLOW}[STEP]${NC} $1"
}

# Function to print success
print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Function to print error
print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if .env exists
if [ ! -f ".env" ]; then
    print_error ".env file not found!"
    echo "Please create .env file from .env.production template"
    exit 1
fi

# Enable maintenance mode
print_step "Enabling maintenance mode..."
$PHP_BIN artisan down --retry=60 || true
print_success "Maintenance mode enabled"

# Pull latest changes (if using git deployment)
if [ -d ".git" ]; then
    print_step "Pulling latest changes from git..."
    git pull origin main
    print_success "Code updated"
fi

# Install/Update Composer dependencies
print_step "Installing Composer dependencies..."
$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction
print_success "Composer dependencies installed"

# Install/Update NPM dependencies and build assets
print_step "Installing NPM dependencies..."
$NPM_BIN ci --production
print_success "NPM dependencies installed"

print_step "Building frontend assets..."
$NPM_BIN run build
print_success "Frontend assets built"

# Clear and cache config
print_step "Optimizing configuration..."
$PHP_BIN artisan config:clear
$PHP_BIN artisan config:cache
print_success "Configuration cached"

# Clear and cache routes
print_step "Optimizing routes..."
$PHP_BIN artisan route:clear
$PHP_BIN artisan route:cache
print_success "Routes cached"

# Clear and cache views
print_step "Optimizing views..."
$PHP_BIN artisan view:clear
$PHP_BIN artisan view:cache
print_success "Views cached"

# Optimize autoloader
print_step "Optimizing autoloader..."
$COMPOSER_BIN dump-autoload --optimize
print_success "Autoloader optimized"

# Run database migrations
print_step "Running database migrations..."
$PHP_BIN artisan migrate --force
print_success "Database migrations completed"

# Clear application cache
print_step "Clearing application cache..."
$PHP_BIN artisan cache:clear
print_success "Application cache cleared"

# Optimize application
print_step "Running final optimizations..."
$PHP_BIN artisan optimize
print_success "Application optimized"

# Restart queue workers
print_step "Restarting queue workers..."
$PHP_BIN artisan queue:restart
print_success "Queue workers restarted"

# Fix permissions
print_step "Fixing storage permissions..."
chmod -R 775 storage bootstrap/cache
print_success "Permissions fixed"

# Disable maintenance mode
print_step "Disabling maintenance mode..."
$PHP_BIN artisan up
print_success "Maintenance mode disabled"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Deployment completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Post-deployment checklist:${NC}"
echo "1. Verify the application is running correctly"
echo "2. Check logs for any errors: tail -f storage/logs/laravel.log"
echo "3. Monitor queue workers: php artisan queue:monitor"
echo "4. Test critical functionality (bookings, payments, SMS)"
echo ""

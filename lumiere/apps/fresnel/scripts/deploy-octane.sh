#!/bin/bash

# DCPrism Octane Deployment Script
# Optimized deployment for production environments

set -e

echo "🚀 Starting DCPrism Octane deployment..."

# Configuration
APP_DIR="/var/www/dcprism"
PHP_FPM_SERVICE="php8.2-fpm"
NGINX_SERVICE="nginx"
OCTANE_SERVICE="dcprism-octane"
RR_BINARY="/usr/local/bin/rr"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
    exit 1
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   error "This script should not be run as root for security reasons"
fi

# Navigate to app directory
cd "$APP_DIR" || error "Cannot access app directory: $APP_DIR"

info "Pulling latest code from repository..."
git fetch origin
git reset --hard origin/main

info "Installing/updating Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

info "Setting up environment and permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

info "Running database migrations..."
php artisan migrate --force

info "Clearing and optimizing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Octane-specific optimizations
info "Optimizing for Octane..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan octane:reload

# Install/update RoadRunner binary
info "Checking RoadRunner installation..."
if [ ! -f "$RR_BINARY" ] || [ "$1" == "--update-rr" ]; then
    info "Installing/updating RoadRunner binary..."
    php artisan octane:install roadrunner --server-binary="$RR_BINARY"
fi

# Create systemd service for Octane if it doesn't exist
if ! systemctl list-unit-files | grep -q "$OCTANE_SERVICE"; then
    info "Creating systemd service for Octane..."
    sudo tee /etc/systemd/system/$OCTANE_SERVICE.service > /dev/null <<EOF
[Unit]
Description=DCPrism Octane Server
After=network.target
Requires=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=$APP_DIR
ExecStart=/usr/bin/php $APP_DIR/artisan octane:start --server=roadrunner --host=127.0.0.1 --port=8000 --workers=4
ExecReload=/bin/kill -USR1 \$MAINPID
Restart=always
RestartSec=5
StandardOutput=append:$APP_DIR/storage/logs/octane.log
StandardError=append:$APP_DIR/storage/logs/octane-error.log

# Resource limits for DCP processing
LimitNOFILE=65536
LimitMEMLOCK=infinity
LimitCORE=infinity

# Environment variables
Environment=LARAVEL_OCTANE=1
Environment=OCTANE_SERVER=roadrunner

[Install]
WantedBy=multi-user.target
EOF

    sudo systemctl daemon-reload
    sudo systemctl enable $OCTANE_SERVICE
    success "Systemd service created and enabled"
fi

# Setup log rotation for Octane logs
if [ ! -f "/etc/logrotate.d/dcprism-octane" ]; then
    info "Setting up log rotation for Octane..."
    sudo tee /etc/logrotate.d/dcprism-octane > /dev/null <<EOF
$APP_DIR/storage/logs/octane*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload $OCTANE_SERVICE > /dev/null 2>&1 || true
    endscript
}
EOF
    success "Log rotation configured"
fi

# Create monitoring script
info "Creating monitoring script..."
sudo tee /usr/local/bin/dcprism-monitor.sh > /dev/null <<'EOF'
#!/bin/bash
# DCPrism Monitoring Script

APP_DIR="/var/www/dcprism"
SERVICE="dcprism-octane"
LOG_FILE="$APP_DIR/storage/logs/monitor.log"

# Check if service is running
if ! systemctl is-active --quiet $SERVICE; then
    echo "$(date): Service $SERVICE is down, restarting..." >> $LOG_FILE
    systemctl restart $SERVICE
fi

# Check memory usage
MEMORY_USAGE=$(ps -eo pid,ppid,cmd,%mem,%cpu --sort=-%mem | grep "octane:start" | head -1 | awk '{print $4}')
if (( $(echo "$MEMORY_USAGE > 80" | bc -l) )); then
    echo "$(date): High memory usage detected: ${MEMORY_USAGE}%, reloading..." >> $LOG_FILE
    systemctl reload $SERVICE
fi

# Check disk space
DISK_USAGE=$(df /var/www | tail -1 | awk '{print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 90 ]; then
    echo "$(date): High disk usage detected: ${DISK_USAGE}%" >> $LOG_FILE
    # Clean up old logs and temp files
    find $APP_DIR/storage/logs -name "*.log" -mtime +7 -delete
    find $APP_DIR/storage/app/temp -type f -mtime +1 -delete
fi
EOF

chmod +x /usr/local/bin/dcprism-monitor.sh

# Add monitoring cron job
if ! crontab -l 2>/dev/null | grep -q "dcprism-monitor"; then
    info "Setting up monitoring cron job..."
    (crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/dcprism-monitor.sh") | crontab -
    success "Monitoring cron job added"
fi

# Restart Octane service
info "Restarting Octane service..."
sudo systemctl restart $OCTANE_SERVICE

# Wait for service to start
sleep 5

# Check if service is running
if systemctl is-active --quiet $OCTANE_SERVICE; then
    success "Octane service is running"
    
    # Test the application
    info "Testing application response..."
    if curl -sf http://127.0.0.1:8000/health > /dev/null; then
        success "Application is responding correctly"
    else
        warning "Application might have issues, check logs"
    fi
else
    error "Failed to start Octane service"
fi

# Display service status
info "Service Status:"
sudo systemctl status $OCTANE_SERVICE --no-pager

info "Checking RoadRunner workers..."
if command -v $RR_BINARY &> /dev/null; then
    $RR_BINARY workers -i -c $APP_DIR/.rr.yaml
else
    warning "RoadRunner binary not found at $RR_BINARY"
fi

success "🎉 DCPrism Octane deployment completed successfully!"

echo ""
info "Useful commands:"
echo "  • View logs: sudo journalctl -u $OCTANE_SERVICE -f"
echo "  • Restart service: sudo systemctl restart $OCTANE_SERVICE"
echo "  • Reload workers: php artisan octane:reload"
echo "  • Monitor RR: $RR_BINARY workers -i -c .rr.yaml"
echo "  • Performance metrics: curl http://127.0.0.1:2112/metrics"
echo ""

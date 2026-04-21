#!/bin/sh

# Set the port in Nginx config from Railway's $PORT env
# Default to 8080 as most cloud providers use it for unprivileged containers
sed -i "s/\[PORT_PLACEHOLDER\]/${PORT:-8080}/g" /etc/nginx/http.d/default.conf

# ──────────────────────────────────────────────
# 1. DATABASE & OPTIMIZATION
# ──────────────────────────────────────────────
echo "[DB] Checking database connectivity..."
# Simple loop to wait for the database (optional but recommended)
NEXT_WAIT_TIME=0
MAX_RETRIES=15
until php artisan db:monitor --max=100 > /dev/null 2>&1 || [ $NEXT_WAIT_TIME -eq $MAX_RETRIES ]; do
  echo "[DB] Waiting for database connection... ($((NEXT_WAIT_TIME+1))/$MAX_RETRIES)"
  sleep 2
  NEXT_WAIT_TIME=$((NEXT_WAIT_TIME+1))
done

echo "[DB] Running migrations..."
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link --force

echo "[LARAVEL] Optimizing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ──────────────────────────────────────────────
# 2. START PYTHON SERVICE
# ──────────────────────────────────────────────
echo "[BRIDGE] Starting Python bridge service..."
# Note: bridge_service.py has hardcoded COM5 and 127.0.0.1:8000
# On Railway, we pass the local app URL. Nginx listens on $PORT.
export PYTHONUNBUFFERED=1
export LARAVEL_URL="http://127.0.0.1:${PORT:-8080}"
# Ensure log directory exists and is writable
touch /tmp/bridge.log
export WS_PORT=8001
python3 bridge_service.py >> /tmp/bridge.log 2>&1 &

# ──────────────────────────────────────────────
# 3. START SERVICES
# ──────────────────────────────────────────────
echo "[SERVER] Starting PHP-FPM..."
php-fpm -D

echo "[SERVER] Starting Nginx on port ${PORT:-8080}..."
# Use exec to ensure Nginx receives signals and script stays alive properly
exec nginx -g "daemon off;"

#!/bin/bash
echo "Deploying..."

# Pull changes
git pull origin main

# Backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend
npm install
npm run build

echo "Deployment complete."

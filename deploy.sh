#!/bin/bash

set -e

# --------------------------------------------------
# Configuration
# --------------------------------------------------

PROJECT_DIR="$HOME/beyond-mrp/www"

# --------------------------------------------------
# Functions
# --------------------------------------------------

maintenance_up() {
    echo "Disabling Maintenance Mode..."
    php artisan up || true
}

# If the script fails, try to bring the application back online
trap maintenance_up ERR

# --------------------------------------------------
# Change to project directory
# --------------------------------------------------

echo "Changing to project directory..."
cd "$PROJECT_DIR"

# --------------------------------------------------
# Enable Maintenance Mode
# --------------------------------------------------

echo "Enabling Maintenance Mode..."
php artisan down --refresh=20

# --------------------------------------------------
# Git configuration
# --------------------------------------------------

echo "Configuring Git..."

git config pull.rebase false
git config merge.ff false
git config core.editor "echo"

# --------------------------------------------------
# Pull latest code
# --------------------------------------------------

echo "Pulling latest code from Git repository..."

git reset --hard
git pull

# --------------------------------------------------
# Check Composer dependencies
# --------------------------------------------------

echo "Checking if Composer dependencies changed..."

COMPOSER_DEPS_CHANGED=0

if ! git diff --quiet ORIG_HEAD HEAD -- composer.json composer.lock; then
    COMPOSER_DEPS_CHANGED=1
fi

if [ ! -f vendor/autoload.php ]; then

    echo "vendor/autoload.php not found."
    echo "Running Composer install..."

    php composer.phar install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --optimize-autoloader

elif [ "$COMPOSER_DEPS_CHANGED" -eq 0 ]; then

    echo "composer.json/composer.lock unchanged."
    echo "Skipping Composer install."

else

    echo "Dependency files changed."
    echo "Running Composer install..."

    php composer.phar install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --optimize-autoloader

fi

# --------------------------------------------------
# Laravel cache
# --------------------------------------------------

echo "Clearing cached Laravel configuration..."
php artisan config:clear

echo "Clearing Laravel application cache..."
php artisan cache:clear

echo "Caching Laravel configuration..."
php artisan config:cache

# --------------------------------------------------
# Database migrations
# --------------------------------------------------

echo "Running database migrations..."
php artisan migrate --force

# --------------------------------------------------
# Frontend build
# --------------------------------------------------

echo "Updating Browserslist database..."
npx update-browserslist-db@latest

echo "Running npm build..."
npm run build

# --------------------------------------------------
# Disable Maintenance Mode
# --------------------------------------------------

maintenance_up

# Disable ERR trap because deployment completed
trap - ERR

# --------------------------------------------------
# Done
# --------------------------------------------------

cd ..
sudo chmod +x deploy.sh

echo "Deployment script completed successfully."

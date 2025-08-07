#!/bin/bash

# Laravel PM System - Devcontainer Setup Script
echo "🚀 Setting up Laravel PM System in devcontainer..."

# Create SQLite database file
echo "🗄️ Setting up SQLite database..."
touch /workspace/database/database.sqlite

# Copy environment file
if [ ! -f /workspace/.env ]; then
    echo "📄 Copying devcontainer environment file..."
    cp /workspace/.env.devcontainer /workspace/.env
fi

# Install Composer dependencies
if [ ! -d /workspace/vendor ]; then
    echo "📦 Installing Composer dependencies..."
    cd /workspace && composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Install NPM dependencies
if [ ! -d /workspace/node_modules ]; then
    echo "📦 Installing NPM dependencies..."
    cd /workspace && npm install
fi

# Generate application key
echo "🔑 Generating application key..."
cd /workspace && php artisan key:generate --force

# Run database migrations and seeders
echo "🗄️ Running database migrations and seeders..."
cd /workspace && php artisan migrate:fresh --seed --force

# Create storage link
echo "🔗 Creating storage link..."
cd /workspace && php artisan storage:link

# Clear and cache config
echo "⚡ Optimizing application..."
cd /workspace && php artisan config:clear
cd /workspace && php artisan config:cache
cd /workspace && php artisan route:cache

# Set permissions
echo "🔐 Setting permissions..."
sudo chown -R vscode:vscode /workspace
sudo chmod -R 775 /workspace/storage
sudo chmod -R 775 /workspace/bootstrap/cache

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📋 Available services:"
echo "   • Laravel App: http://localhost:8000"
echo "   • Vite Dev Server: http://localhost:5173" 
echo "   • SQLite Database: /workspace/database/database.sqlite"
echo "   • Redis: localhost:6379"
echo "   • MailHog: http://localhost:8025"
echo ""
echo "🚀 To start the application:"
echo "   1. Run: php artisan serve --host=0.0.0.0 --port=8000"
echo "   2. In new terminal: npm run dev -- --host=0.0.0.0"
echo ""
echo "💡 Tip: Use 'php artisan' to run Laravel commands"
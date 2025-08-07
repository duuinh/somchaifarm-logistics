# Laravel PM System - Dev Container

This dev container provides a complete development environment for the Laravel PM System.

## What's Included

- **PHP 8.2** with Laravel extensions
- **Node.js 18** with npm
- **Composer** for PHP dependency management
- **SQLite** database (lightweight, file-based)
- **Redis** for caching and sessions
- **MailHog** for email testing
- **VS Code extensions** for Laravel, Vue.js, and Tailwind CSS

## Services

| Service | Port | Description |
|---------|------|-------------|
| Laravel App | 8000 | Main application |
| Vite Dev Server | 5173 | Frontend build tool |
| Redis | 6379 | Cache and sessions |
| MailHog Web | 8025 | Email testing interface |
| MailHog SMTP | 1025 | SMTP server for emails |

## Quick Start

### Option 1: GitHub Codespaces
1. Fork or clone this repository
2. Click "Code" → "Codespaces" → "Create codespace"
3. Wait for the container to build and setup to complete
4. Start the application:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
5. In a new terminal, start the frontend:
   ```bash
   npm run dev -- --host=0.0.0.0
   ```

### Option 2: Local VS Code with Remote Containers
1. Install VS Code and the "Remote - Containers" extension
2. Clone this repository
3. Open in VS Code
4. Click "Reopen in Container" when prompted
5. Wait for setup to complete
6. Start the services as above

## Database

The container uses SQLite by default, which is:
- **Lightweight**: No separate database server needed
- **Portable**: Database file is included in the workspace
- **Fast**: Perfect for development and testing

Database file location: `/workspace/database/database.sqlite`

## Pre-configured Features

- ✅ Auto-generated application key
- ✅ Database migrations run automatically
- ✅ Sample data seeded
- ✅ Storage link created
- ✅ Optimized configurations cached

## Available Commands

```bash
# Laravel commands
php artisan migrate          # Run migrations
php artisan db:seed         # Seed database
php artisan serve           # Start development server
php artisan tinker          # Interactive shell

# Frontend commands
npm run dev                 # Start Vite dev server
npm run build              # Build for production
npm run preview            # Preview production build

# Database
php artisan migrate:fresh --seed  # Reset and seed database
```

## Environment Variables

The dev container uses `.env.devcontainer` with pre-configured settings for:
- SQLite database connection
- Redis connection
- MailHog email configuration
- Debug mode enabled
- Thai locale support

## Troubleshooting

### Container won't start
- Ensure Docker is running
- Try rebuilding: Command Palette → "Remote-Containers: Rebuild Container"

### Application errors
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Clear caches: `php artisan config:clear && php artisan cache:clear`

### Database issues
- Reset database: `php artisan migrate:fresh --seed`
- Check database file exists: `ls -la database/database.sqlite`

## Development Workflow

1. **Backend changes**: Edit PHP files, migrations run automatically
2. **Frontend changes**: Edit Vue/CSS files, Vite will hot-reload
3. **Database changes**: Create migrations with `php artisan make:migration`
4. **New features**: Use Laravel Artisan commands to scaffold

## VS Code Extensions Included

- PHP Intelephense (PHP IntelliSense)
- Laravel Blade Syntax
- Vue Language Features (Volar)
- Tailwind CSS IntelliSense
- Laravel Artisan
- Prettier Code Formatter

Happy coding! 🚀
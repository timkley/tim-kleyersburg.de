# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

Tim Kleyersburg's personal website (tim-kleyersburg.de) built with Laravel 12, using Livewire v3 for interactive components and Prezet for markdown-based articles. The site features a modular architecture with TailwindCSS 4 for styling and code highlighting via torchlight.dev.

## Quick Start

### Prerequisites
- PHP 8.4+
- Node.js 18+
- Composer
- npm or pnpm

### Initial Setup
```bash
composer install
npm install
cp .env.example .env  # if .env doesn't exist
php artisan key:generate
npm run dev
```

The site runs via Laravel Herd at `https://tim-kleyersburg-de.test` (use the `get-absolute-url` Boost tool for accurate URLs).

## Development Commands

### Asset Building
```bash
npm run dev         # Vite dev server with hot reload
npm run build       # Production build for deployment
```

### Full-Stack Development
```bash
composer run dev    # Concurrent queue listener + Vite (recommended)
```
This command runs both the queue worker and Vite dev server simultaneously using concurrently.

### Testing
```bash
php artisan test                    # Run all Pest tests
composer test                       # Run tests excluding network group, parallel
php artisan test --filter=TestName # Run specific test
php artisan test tests/Feature/     # Run specific directory
```

### Code Quality & Static Analysis
```bash
composer phpstan    # Run Larastan (PHPStan for Laravel)
composer pint       # Run Laravel Pint code formatter
composer rector     # Run Rector (dry-run mode)
composer prepush    # Run all QA tools (PHPStan + tests + Pint)
```

### Database & Development
```bash
php artisan migrate           # Run migrations
php artisan tinker           # Interactive PHP REPL
php artisan make:livewire    # Generate Livewire component
php artisan make:model       # Generate model with factory/seeder options
```

## Architecture Overview

### Laravel 12 Structure
- **Streamlined file structure**: No `app/Http/Middleware/` or `app/Console/Kernel.php`
- **Bootstrap configuration**: Middleware and providers registered in `bootstrap/app.php` and `bootstrap/providers.php`
- **Auto-discovery**: Console commands in `app/Console/Commands/` are automatically registered

### Core Components

#### Livewire v3 Integration
- Components namespace: `App\Livewire\*`
- Page components: `App\Livewire\Pages\` (Home, Einmaleins)
- Article components: `App\Livewire\Articles\` (Index, Show)
- Uses wire:navigate for SPA-like navigation

#### Prezet Blog System
- Markdown articles stored in `resources/articles/content/`
- Custom routes for image serving (`/articles/img/{path}`)
- OG image generation (`/articles/ogimage/{slug}`)
- Configuration in `config/prezet.php`

#### Modular Architecture
- Custom modules in `modules/Holocron/`
- Each module has its own ServiceProvider
- Shared views loaded via `loadViewsFrom()`

### Frontend Stack
- **TailwindCSS 4**: Uses `@import "tailwindcss"` (not v3 directives)
- **Vite**: Asset bundling and hot reload
- **Flux UI Pro**: Full component library access
- **Alpine.js**: Bundled with Livewire v3 (includes persist, intersect, collapse, focus plugins)

### Key Services
- **Weather Service**: NASA API integration
- **Scrobble Model**: Last.fm integration
- **Notifications**: Discord channel integrations (school, personal)

## Folder Structure Cheat Sheet

```
app/
├── Console/Commands/     # Auto-discovered Artisan commands
├── Data/                 # Data transfer objects
├── Http/Middleware/      # Custom middleware (minimal in v12)
├── Livewire/            # Livewire v3 components
│   ├── Articles/        # Article listing and display
│   └── Pages/           # Page components
├── Models/              # Eloquent models
├── Notifications/       # Laravel notifications
├── Providers/           # Service providers
└── Services/            # Application services

modules/Holocron/        # Modular architecture
├── Bookmarks/
├── Dashboard/
├── Gear/
└── [other modules]/

resources/
├── articles/content/    # Prezet markdown articles
└── views/              # Blade templates

tests/
├── Feature/            # Feature tests (Pest)
└── Unit/               # Unit tests (Pest)
```

## Installed Packages & Versions

### Core Framework
- **PHP**: 8.4
- **Laravel**: v12
- **Livewire**: v3.5
- **Flux UI Pro**: v2.0

### Content & Blog
- **Prezet**: v1.2 (markdown blog system)
- **Spatie Laravel Feed**: v4.4 (RSS feeds)

### Frontend
- **TailwindCSS**: v4.0
- **Alpine.js**: v3.14 (bundled with Livewire)
- **Vite**: v6.3

### Testing & QA
- **Pest**: v4.0 (testing framework)
- **Larastan**: v3.1 (PHPStan for Laravel)
- **Laravel Pint**: v1.20 (code formatter)
- **Rector**: v2.0 (automated refactoring)

### Monitoring & Integration
- **Laravel Pulse**: v1.3 (application monitoring)
- **Bugsnag Laravel**: v2.29 (error tracking)
- **Laravel Scout**: v10.15 + Typesense (search)
- **Laravel Reverb**: v1.0 (WebSocket server)

## Testing & QA Workflow

### Pest Testing Framework
All tests use Pest syntax. Example Livewire test:
```php
it('displays articles index', function () {
    $response = $this->get('/articles');
    $response->assertSeeLivewire(\App\Livewire\Articles\Index::class);
});
```

### Running Tests
- Tests run in parallel by default
- Network tests excluded in composer script
- Use `--filter` for targeted testing after changes

### Static Analysis Pipeline
1. **Larastan**: Type analysis with Laravel-specific rules
2. **Rector**: Code modernization (dry-run only)
3. **Pint**: PSR-12 code formatting

### Pre-push Workflow
Run `composer prepush` before pushing changes - executes PHPStan, tests, and Pint in sequence.

## Environment & Configuration

### Key Environment Variables
```env
APP_URL=https://tim-kleyersburg-de.test
APP_NAME="Tim Kleyersburg"

# Prezet Configuration
PREZET_FILESYSTEM_DISK=blog
PREZET_SITEMAP_ORIGIN=${APP_URL}

# External Services
BUGSNAG_API_KEY=
```

### Database
- Default: SQLite (`database/database.sqlite`)
- Auto-created in post-install script
- Use `php artisan migrate` to run migrations

### File Storage
- **blog** disk: Used for Prezet articles and images
- Image routes cached for 1 year (`public, max-age=31536000`)

## Laravel Boost Integration

This project uses Laravel Boost with several specialized tools:

- `get-absolute-url`: Get correct URLs for Herd environment
- `search-docs`: Search Laravel ecosystem documentation (pass package arrays for filtering)
- `browser-logs`: Read browser console logs and errors
- `tinker`: Execute PHP code for debugging
- `database-query`: Query database directly

## Troubleshooting & FAQ

### Common Issues

**"Vite manifest not found" Error**
```bash
npm run build  # or ask user to run npm run dev
```

**Frontend changes not reflecting**
- Ensure `npm run dev` is running
- Try `composer run dev` for full-stack development

**Test failures**
- Run specific test with `--filter=TestName`
- Check if network tests are interfering (excluded by default)

**Static analysis errors**
- Run `composer pint` to auto-fix formatting
- Use `composer phpstan` for detailed type analysis

### Development Tips

1. **Use Boost tools**: Leverage `search-docs` for Laravel ecosystem documentation
2. **Livewire debugging**: Check browser logs with `browser-logs` tool
3. **Database queries**: Use `database-query` tool for direct DB access
4. **Code generation**: Prefer `php artisan make:*` commands with appropriate options

### Code Style Guidelines

From `.junie/guidelines.md`:
- Always use `declare(strict_types=1);` in PHP files
- Use PHP 8 constructor property promotion
- Follow existing Livewire v3 patterns (`wire:model.live`, `$this->dispatch()`)
- Use Flux UI components when available
- Strict return type declarations required

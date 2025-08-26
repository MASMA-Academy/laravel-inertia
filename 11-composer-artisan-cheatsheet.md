# 🚀 Composer & PHP Artisan Commands Cheatsheet

> **Note**: This cheatsheet contains commonly used commands. Always verify
> commands with the latest Laravel documentation as some commands may change
> between versions or may not exist in your Laravel version.

## 📦 Composer Commands

### Package Management

```bash
# Install dependencies from composer.json
composer install
# When: After cloning a project, when setting up on new machine

# Install dependencies (production only, no dev dependencies)
composer install --no-dev
# When: Deploying to production server, building Docker images

# Update all packages to latest versions
composer update
# When: Want to get latest features, security updates, or bug fixes

# Update specific package
composer update vendor/package-name
# When: Need to update only one package without affecting others

# Add new package
composer require vendor/package-name
# When: Adding new functionality like authentication, payment processing

# Add new package with specific version
composer require vendor/package-name:^2.0
# When: Need compatibility with specific Laravel version or PHP version

# Add new package as dev dependency
composer require --dev vendor/package-name
# When: Adding testing tools, code quality tools, or development helpers

# Remove package
composer remove vendor/package-name
# When: No longer need a package, cleaning up dependencies

# Show installed packages
composer show
# When: Checking what's installed, reviewing project dependencies

# Show specific package details
composer show vendor/package-name
# When: Checking package version, dependencies, or license

# Check for outdated packages
composer outdated
# When: Planning updates, checking for security vulnerabilities

# Update composer itself
composer self-update
# When: Getting latest composer features or bug fixes
```

### Project Management

```bash
# Create new project from package
composer create-project vendor/package-name project-directory
# When: Starting a project based on existing template or boilerplate

# Create new Laravel project
composer create-project laravel/laravel project-name
# When: Starting a new Laravel project from scratch

# Create new Laravel project with specific version
composer create-project laravel/laravel:^10.0 project-name
# When: Need to use specific Laravel version for compatibility

# Install Laravel Installer globally
composer global require laravel/installer
# When: Want to use 'laravel new' command for faster project creation

# Create new project using Laravel Installer
laravel new project-name
# When: Quick project creation with Laravel Installer

# Create new project with Breeze and Vue
laravel new project-name
cd project-name
composer require laravel/breeze --dev
php artisan breeze:install vue
# When: Starting a project that needs authentication and Vue.js frontend

# Create new project with Breeze and React
laravel new project-name
cd project-name
composer require laravel/breeze --dev
php artisan breeze:install react
# When: Starting a project that needs authentication and React frontend

# Create new project with Breeze and API
laravel new project-name
cd project-name
composer require laravel/breeze --dev
php artisan breeze:install api
# When: Building an API-only application with authentication
```

### Dependency Analysis

```bash
# Show dependency tree
composer depends vendor/package-name
# When: Understanding why a package is installed, debugging dependency issues

# Show reverse dependencies
composer why vendor/package-name
# When: Checking which packages depend on specific package

# Show package conflicts
composer why-not vendor/package-name
# When: Package installation fails, need to understand conflicts

# Validate composer.json
composer validate
# When: Before committing changes, checking configuration syntax

# Check for security vulnerabilities
composer audit
# When: Security audit, before production deployment

# Fix security vulnerabilities
composer audit --fix
# When: Automatically fixing security issues when possible
```

### Development Tools

```bash
# Install development dependencies
composer install --dev
# When: Setting up development environment, need testing tools

# Run scripts defined in composer.json
composer run-script script-name
# When: Executing custom build scripts, testing commands

# Show available scripts
composer run
# When: Discovering available scripts, checking project automation

# Install with optimized autoloader
composer install --optimize-autoloader
# When: Production deployment, improving performance

# Generate optimized autoloader
composer dump-autoload --optimize
# When: After adding new classes, improving autoloader performance

# Clear composer cache
composer clear-cache
# When: Package installation issues, corrupted cache
```

---

## 🎯 PHP Artisan Commands

### Application Management

```bash
# Start development server
php artisan serve
# When: Starting local development, testing application locally

# Start development server on specific port
php artisan serve --port=8080
# When: Default port 8000 is busy, need to use different port

# Start development server on specific host
php artisan serve --host=0.0.0.0
# When: Need to access app from other devices on network, Docker containers

# Show application information
php artisan about
# When: Checking Laravel version, PHP version, or debugging environment

# Show application version
php artisan --version
# When: Quick check of Laravel version

# Clear application cache
php artisan cache:clear
# When: Cache is corrupted, after updating cache configuration

# Clear config cache
php artisan config:clear
# When: After changing .env file, config files, or environment variables

# Clear route cache
php artisan route:clear
# When: After adding new routes, route caching issues

# Clear view cache
php artisan view:clear
# When: After updating Blade templates, view not reflecting changes

# Clear all caches
php artisan optimize:clear
# When: General troubleshooting, after major configuration changes

# Optimize application for production
php artisan optimize
# When: Deploying to production, improving performance

# Show environment
php artisan env
# When: Debugging environment issues, checking configuration
```

### Database Management

```bash
# Run database migrations
php artisan migrate
# When: Setting up database for first time, after pulling new migrations

# Run migrations with seed
php artisan migrate --seed
# When: Setting up fresh database with sample data

# Rollback last migration
php artisan migrate:rollback
# When: Need to undo last migration, testing rollback functionality

# Rollback specific number of migrations
php artisan migrate:rollback --step=3
# When: Need to undo multiple migrations, testing specific state

# Reset all migrations
php artisan migrate:reset
# When: Want to start fresh, testing migration process

# Refresh migrations (reset + migrate)
php artisan migrate:refresh
# When: Testing migrations, need fresh database structure

# Refresh migrations with seed
php artisan migrate:refresh --seed
# When: Testing with fresh data, development setup

# Show migration status
php artisan migrate:status
# When: Checking which migrations have run, debugging migration issues

# Create new migration
php artisan make:migration create_users_table
# When: Adding new table to database

# Create new migration with table name
php artisan make:migration add_columns_to_users_table --table=users
# When: Modifying existing table structure

# Create new seeder
php artisan make:seeder UserSeeder
# When: Need sample data for testing or development

# Run specific seeder
php artisan db:seed --class=UserSeeder
# When: Need specific data, testing individual seeder

# Run all seeders
php artisan db:seed
# When: Setting up complete sample dataset

# Show database connection
php artisan db:show
# When: Debugging database connection issues

# Monitor database queries
# Note: Laravel doesn't have a db:monitor command
# Use Laravel Telescope or custom logging for query monitoring
# When: Performance testing, debugging slow queries
```

### Model & Controller Management

```bash
# Create new model
php artisan make:model User
# When: Adding new entity to application, need Eloquent model

# Create model with migration
php artisan make:model User -m
# When: Creating model and database table together

# Create model with migration and seeder
php artisan make:model User -ms
# When: Need model, table, and sample data for testing

# Create model with migration, seeder, and factory
php artisan make:model User -msf
# When: Complete model setup with testing data generation

# Create model with all resources
php artisan make:model User -a
# When: Full CRUD setup - model, migration, seeder, factory, controller, resource

# Create new controller
php artisan make:controller UserController
# When: Adding new functionality, handling HTTP requests

# Create resource controller
php artisan make:controller UserController --resource
# When: Need full CRUD operations (index, create, store, show, edit, update, destroy)

# Create API resource controller
php artisan make:controller UserController --api
# When: Building API endpoints, don't need create/edit forms

# Create controller with model
php artisan make:controller UserController --model=User
# When: Controller will work with specific model, automatic type-hinting

# Create new request class
php artisan make:request StoreUserRequest
# When: Need custom validation rules, form request handling

# Create new resource class
php artisan make:resource UserResource
# When: Transforming model data for API responses

# Create new collection resource
php artisan make:resource UserCollection
# When: Transforming collections of models for API responses

# Create new API resource
php artisan make:resource UserResource --api
# When: Building API with consistent response format
```

### Route Management

```bash
# List all routes
php artisan route:list
# When: Debugging routing issues, checking all available endpoints

# List routes with specific method
php artisan route:list --method=GET
# When: Checking only GET routes, debugging specific HTTP methods

# List routes with specific name
php artisan route:list --name=users
# When: Finding routes with specific names, debugging named routes

# Cache routes for production
php artisan route:cache
# When: Deploying to production, improving route performance

# Clear route cache
php artisan route:clear
# When: After adding new routes, route caching issues

# Show route information
php artisan route:show route-name
# When: Debugging specific route, checking route parameters
```

### Authentication & Authorization

```bash
# Install Laravel Breeze
php artisan breeze:install
# When: Adding authentication to new project, need login/register system

# Install Laravel Breeze with Vue
php artisan breeze:install vue
# When: Building SPA with Vue.js frontend and authentication

# Install Laravel Breeze with React
php artisan breeze:install react
# When: Building SPA with React frontend and authentication

# Install Laravel Breeze with API
php artisan breeze:install api
# When: Building API-only application with authentication

# Install Laravel Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
# When: Building API with token-based authentication

# Install Laravel Passport
php artisan passport:install
# When: Building OAuth2 server, need full OAuth implementation

# Create new policy
php artisan make:policy UserPolicy
# When: Adding authorization rules, controlling user access

# Create policy with model
php artisan make:policy UserPolicy --model=User
# When: Policy will work with specific model, automatic model binding

# Create new middleware
php artisan make:middleware CheckRole
# When: Need custom authentication/authorization logic

# Create new guard (custom implementation)
# Note: Laravel doesn't have a make:guard command
# You need to implement guards manually in config/auth.php
# When: Need custom authentication method, multiple auth systems
```

### Testing

```bash
# Run all tests
php artisan test
# When: Before committing code, checking if changes broke anything

# Run tests in specific directory
php artisan test tests/Feature
# When: Testing specific feature area, faster feedback during development

# Run specific test file
php artisan test tests/Feature/UserTest.php
# When: Debugging specific test, quick iteration during development

# Run tests with coverage
php artisan test --coverage
# When: Checking test coverage, quality assurance

# Run tests in parallel
php artisan test --parallel
# When: Large test suite, want faster execution

# Create new test
php artisan make:test UserTest
# When: Adding tests for new functionality

# Create unit test
php artisan make:test UserTest --unit
# When: Testing individual units, isolated functionality

# Create test with model
php artisan make:test UserTest --model=User
# When: Testing model-related functionality, automatic model setup
```

### Queue Management

```bash
# Start queue worker
php artisan queue:work
# When: Processing background jobs, emails, notifications

# Start queue worker with specific queue
php artisan queue:work --queue=high,default,low
# When: Need priority-based job processing

# Start queue worker with specific connection
php artisan queue:work --connection=redis
# When: Using Redis for queue storage, better performance

# Start queue worker in daemon mode
php artisan queue:work --daemon
# When: Production environment, continuous job processing

# Show queue status
php artisan queue:status
# When: Monitoring queue health, debugging job issues

# Clear all queues
php artisan queue:clear
# When: Testing, need clean queue state

# Clear specific queue
php artisan queue:clear --queue=default
# When: Clearing specific queue, testing specific functionality

# Retry failed jobs
php artisan queue:retry all
# When: After fixing job issues, reprocessing failed jobs

# Retry specific failed job
php artisan queue:retry 1 2 3
# When: Testing specific jobs, selective reprocessing

# Create new job
php artisan make:job ProcessUserData
# When: Adding background processing functionality

# Create queued job
php artisan make:job ProcessUserData --queued
# When: Job should be processed in background queue
```

### File Management

```bash
# Create new command
php artisan make:command SendEmails
# When: Adding custom CLI commands, scheduled tasks

# Create new console command
php artisan make:command SendEmails --command=emails:send
# When: Need specific command signature, custom CLI interface

# Create new event
php artisan make:event UserRegistered
# When: Need to notify other parts of app about user actions

# Create new listener
php artisan make:listener SendWelcomeEmail
# When: Reacting to events, sending emails, notifications

# Create new notification
php artisan make:notification WelcomeNotification
# When: Sending user notifications, emails, SMS, database notifications

# Create new mail class
php artisan make:mail WelcomeMail
# When: Sending formatted emails, need custom email templates

# Create new job
php artisan make:job ProcessOrder
# When: Background processing, heavy operations

# Create new service provider
php artisan make:provider CustomServiceProvider
# When: Registering services, bootstrapping custom functionality

# Create new middleware
php artisan make:middleware CheckPermission
# When: Adding authentication/authorization logic, request filtering

# Create new channel
php artisan make:channel OrderChannel
```

### Maintenance Mode

```bash
# Enable maintenance mode
php artisan down
# When: Deploying updates, performing maintenance, database migrations

# Enable maintenance mode with secret token
php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"
# When: Need to allow specific users to access app during maintenance

# Enable maintenance mode with allowed IPs
php artisan down --allow=127.0.0.1 --allow=192.168.0.0/16
# When: Need to allow developers or specific networks access during maintenance

# Enable maintenance mode with render callback
php artisan down --render="errors::503"
# When: Custom maintenance page, specific error handling

# Disable maintenance mode
php artisan up
# When: Maintenance complete, ready to serve users again

# Show maintenance mode status
php artisan down --status
# When: Checking if app is in maintenance mode, debugging
```

### Storage Management

```bash
# Create storage link
php artisan storage:link
# When: Setting up file uploads, need public access to storage files

# Clear compiled views
php artisan view:clear
# When: After updating Blade templates, view not reflecting changes

# Clear application cache
php artisan cache:clear
# When: Cache is corrupted, after updating cache configuration

# Clear config cache
php artisan config:clear
# When: After changing .env file, config files, or environment variables

# Clear route cache
php artisan route:clear
# When: After adding new routes, route caching issues

# Clear all caches
php artisan optimize:clear
# When: General troubleshooting, after major configuration changes

# Publish vendor assets
php artisan vendor:publish
# When: Installing packages that need configuration files or assets

# Publish specific vendor assets
php artisan vendor:publish --tag=config
# When: Need only configuration files from package, not all assets
```

### Custom Commands

```bash
# List all available commands
php artisan list
# When: Discovering available commands, exploring Laravel functionality

# Show command help
php artisan help command-name
# When: Need detailed information about specific command

# Show command signature
php artisan list --format=json
# When: Need machine-readable command list, automation scripts

# Create new command
php artisan make:command CustomCommand
# When: Adding custom CLI functionality, scheduled tasks

# Create command with signature
php artisan make:command CustomCommand --command=custom:run
# When: Need specific command signature, custom CLI interface
```

---

## 🔧 Common Development Workflows

### Setting Up New Laravel Project

```bash
# 1. Create new project
laravel new my-project
cd my-project
composer require laravel/breeze --dev
php artisan breeze:install vue

# 2. Navigate to project
cd my-project

# 3. Install dependencies
composer install
npm install

# 4. Set up environment
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=my_project
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Run migrations
php artisan migrate

# 7. Build assets
npm run build

# 8. Start development servers
php artisan serve
npm run dev
```

### Daily Development Workflow

```bash
# Start development
php artisan serve
npm run dev

# Create new feature
php artisan make:controller FeatureController --resource
php artisan make:model Feature -m
php artisan make:request StoreFeatureRequest
php artisan make:resource FeatureResource

# Update database
php artisan make:migration add_field_to_features_table
php artisan migrate

# Test changes
php artisan test

# Clear caches if needed
php artisan optimize:clear
```

### Production Deployment

```bash
# 1. Install production dependencies
composer install --no-dev --optimize-autoloader

# 2. Build assets
npm run build

# 3. Optimize application
php artisan optimize

# 4. Cache routes and config
php artisan route:cache
php artisan config:cache

# 5. Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 📚 Useful Tips

### Composer Tips

- Use `composer install --no-dev` in production
- Run `composer dump-autoload --optimize` for better performance
- Use `composer audit` to check for security vulnerabilities
- Use `composer outdated` to check for package updates

### Artisan Tips

- Use `php artisan list` to discover available commands
- Use `php artisan help command-name` for detailed help
- Use `php artisan optimize:clear` when things get weird
- Use `php artisan serve --host=0.0.0.0` to access from other devices
- Use `php artisan migrate:status` to check migration state

### Performance Tips

- Use `php artisan optimize` in production
- Use `php artisan route:cache` and `php artisan config:cache` in production
- Use `composer install --optimize-autoloader` in production
- Use `npm run build` instead of `npm run dev` in production

---

## 🚨 Troubleshooting

### Common Issues

```bash
# Permission denied errors
sudo chown -R $USER:$USER storage bootstrap/cache
chmod -R 755 storage bootstrap/cache

# Class not found errors
composer dump-autoload

# Cache issues
php artisan optimize:clear

# Database connection issues
php artisan config:clear
php artisan cache:clear

# Route not found
php artisan route:clear
php artisan route:cache

# View not found
php artisan view:clear
```

### Debug Commands

```bash
# Show application debug info
php artisan about

# Show environment
php artisan env

# Show configuration (use config:cache to see cached config)
# Note: Laravel doesn't have a config:show command
# Use php artisan config:clear to clear cached config

# Show routes
php artisan route:list

# Show database connection
php artisan db:show

# Show queue status
php artisan queue:status
```

This cheatsheet covers the most commonly used Composer and PHP Artisan commands
for Laravel development. Keep it handy for quick reference during development!

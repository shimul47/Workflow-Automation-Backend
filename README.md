# Workflow Backend API

A robust and scalable backend API built with Laravel for managing workflows, user roles, permissions, and company hierarchies.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database](#database)
- [API Documentation](#api-documentation)
- [Project Structure](#project-structure)
- [Authentication](#authentication)
- [Authorization](#authorization)
- [Testing](#testing)
- [Contributing](#contributing)

## Features

- **User Management**: Complete user authentication and profile management
- **Role-Based Access Control (RBAC)**: Flexible role and permission system
- **Company Hierarchy**: Support for multi-company deployments
- **Menu Management**: Dynamic menu configuration with role-based access
- **API Authentication**: Secure token-based authentication using Laravel Sanctum
- **Database Migrations**: Automated database schema management
- **Testing**: Comprehensive test suite with Pest PHP

## Tech Stack

- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Testing**: Pest PHP
- **Package Manager**: Composer

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 8.0+
- Node.js 16+

## Installation

1. ### Clone the repository

    ```bash
    git clone https://github.com/shimul47/Workflow-Automation-Backend.git
    cd workflow-backend
    ```

2. ### Install dependencies

    ```bash
    composer install
    ```

3. ### Create environment file

    ```bash
    cp .env.example .env
    ```

4. ### Generate application key

    ```bash
    php artisan key:generate
    ```

5. ### Run migrations

    ```bash
    php artisan migrate
    ```

6. ### Seed the database (optional)

    ```bash
    php artisan db:seed
    ```

7. ### Start the development server
    ```bash
    php artisan serve
    ```

The application will be available at `http://localhost:8000`

## Configuration

### Environment Variables

Key environment variables in `.env`:

```
APP_NAME=Workflow
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=workflow
DB_USERNAME= your phpmyadmin username
DB_PASSWORD= your phpmyadmin password

SANCTUM_STATEFUL_DOMAINS=localhost
```

Refer to [config/](config/) directory for additional configuration files.

## Database

### Schema Overview

The application includes the following core tables:

- **users**: User accounts and authentication
- **companies**: Company information
- **roles**: User roles
- **permissions**: System permissions
- **role_user**: Many-to-many relationship between roles and users
- **permission_role**: Many-to-many relationship between permissions and roles
- **menus**: Application menu configuration
- **menu_role**: Menu visibility per role
- **personal_access_tokens**: API token storage (Sanctum)

### Migrations

Run migrations with:

```bash
php artisan migrate
```

Rollback migrations with:

```bash
php artisan migrate:rollback
```

Create a new migration:

```bash
php artisan make:migration create_table_name
```

## API Documentation

### Authentication

All API endpoints require authentication via Bearer token. Include the token in the Authorization header:

```
Authorization: Bearer your_token_here
```

### Available Models

- **User**: User accounts with roles and permissions
- **Company**: Organization/company entities
- **Role**: User roles for access control
- **Permission**: System permissions
- **Menu**: Navigation menu items

### Example Endpoints

API routes are defined in [routes/api.php](routes/api.php)

## Project Structure

```
app/
├── Http/
│   ├── Controllers/     # API controllers
│   ├── Middleware/      # Custom middleware
│   └── Requests/        # Form request validation
├── Models/              # Eloquent models
│   ├── User.php
│   ├── Company.php
│   ├── Role.php
│   ├── Permission.php
│   └── Menu.php
└── Notifications/       # Notification classes

config/                  # Application configuration
database/
├── migrations/          # Database migrations
├── factories/           # Model factories for testing
└── seeders/             # Database seeders

routes/
├── api.php              # API routes
├── auth.php             # Authentication routes
├── web.php              # Web routes
└── console.php          # Console commands

tests/
├── Feature/             # Feature tests
└── Unit/                # Unit tests

storage/
├── app/                 # Application storage
└── logs/                # Application logs
```

## Authentication

This application uses **Laravel Sanctum** for API authentication.

### Login

POST `/api/login`

```json
{
    "email": "user@example.com",
    "password": "password"
}
```

### Response

```json
{
    "token": "token_string",
    "user": {
        /* user object */
    }
}
```

### Logout

POST `/api/logout`

## Authorization

The application implements a comprehensive RBAC system:

- **Permissions**: Granular actions (e.g., `create-user`, `edit-role`)
- **Roles**: Groups of permissions (e.g., `admin`, `manager`)
- **Users**: Assigned one or more roles

Check permissions in controllers:

```php
if ($user->can('create-user')) {
    // Allow action
}
```

## Testing

Run the test suite with Pest:

```bash
php artisan test
```

Run specific test file:

```bash
php artisan test tests/Feature/ExampleTest.php
```

Run with coverage:

```bash
./vendor/bin/pest --coverage
```

## Contributing

1. Create a feature branch (`git checkout -b feature/your_name`)
2. Commit changes (`git commit -m 'What you have updated.`)
3. Push to branch (`git push origin feature/your_name`)
4. Open a Pull Request

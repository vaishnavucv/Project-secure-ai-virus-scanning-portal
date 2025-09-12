# Overview

This is a PHP-based web portal application that provides authentication and role-based access control. The system supports two user roles - admin and user - with different permission levels. The application is built using vanilla PHP with MySQL for data persistence and includes a complete user management system with secure password hashing.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Frontend Architecture
The application uses a traditional server-side rendered approach with PHP generating HTML pages. The public directory contains the web-accessible files, with a simple structure that separates public access from internal logic.

## Backend Architecture
The system follows a straightforward PHP architecture with:
- **Session-based authentication**: Uses PHP sessions to maintain user login state
- **Role-based access control**: Two distinct user roles (admin/user) with different permission levels
- **Secure password handling**: Implements bcrypt password hashing using PHP's `password_hash()` function
- **Database abstraction**: Uses a configuration layer for database connectivity with environment variable support

## Data Storage
- **MySQL database**: Primary data storage for user accounts and application data
- **Schema management**: Database structure defined in `schema.sql` with pre-seeded demo accounts
- **Connection management**: Configurable database credentials through `config.php` or environment variables

## Authentication & Authorization
- **Secure login system**: Password verification using `password_verify()` against bcrypt hashes
- **Session management**: PHP sessions track authenticated users and their roles
- **Admin privileges**: Administrative users can access additional functionality like user management
- **Demo accounts**: Pre-configured test accounts for both admin and regular user roles

## File Upload System
The application includes a file upload mechanism with an `uploads/` directory, suggesting document or media management capabilities within the portal.

# External Dependencies

## Database
- **MySQL**: Required for user data storage and application state
- **Schema initialization**: Database setup via SQL script with demo data

## Runtime Environment
- **PHP**: Server-side scripting engine (compatible with built-in development server)
- **Web server**: Can run on PHP's built-in server or traditional web servers like Apache/Nginx

## Configuration
- **Environment variables**: Optional configuration via `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- **Fallback configuration**: Default database settings in `config.php`

## Development Tools
- **MySQL command-line client**: For database initialization and management
- **PHP CLI**: For running the built-in development server
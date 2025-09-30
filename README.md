# Secure AI Virus Scanning Portal

A comprehensive web-based virus scanning portal built with PHP and MySQL. Users can upload files for scanning via the VirusTotal API and receive AI-generated summary reports using OpenAI API. The application includes user authentication, role-based access control, scan history tracking, and PDF report export functionality.

## Features

- **User Authentication**: Secure login system with role-based access (Admin/User)
- **File Upload & Scanning**: Upload files for virus scanning using VirusTotal API
- **AI-Powered Reports**: Generate AI summaries of scan results using OpenAI API
- **Scan History**: Track and view all previous file scans
- **PDF Export**: Export scan reports as PDF documents
- **Admin Panel**: Manage users and API keys
- **Responsive Design**: Modern, mobile-friendly interface
- **Automated Installation**: One-command setup with comprehensive testing
- **Cross-Platform Compatibility**: Works with both MySQL and PostgreSQL
- **Error Handling**: Robust error handling and compatibility fixes

## Demo Accounts
- **Admin**: `admin@secure.com` / password: `password`
- **User**: `user1@secure.com` / password: `password`

## Recent Improvements

### ✅ Latest Updates (v2.0)

- **Automated Installation**: Complete one-command setup with `install.sh`
- **Comprehensive Testing**: Built-in test suite with `test_install.sh`
- **Database Compatibility**: Added MySQL schema alongside PostgreSQL
- **mbstring Fix**: Resolved compatibility issues with multibyte string functions
- **Enhanced Error Handling**: Better error messages and troubleshooting guides
- **API Integration**: Streamlined VirusTotal API key management
- **Documentation**: Complete installation and troubleshooting guides
- **Cross-Platform Support**: Works on Ubuntu, Debian, and other Linux distributions

### 🔧 Technical Improvements

- Fixed `mb_substr()` function compatibility issues
- Added automatic dependency detection and installation
- Improved database connection handling
- Enhanced security with proper input validation
- Added comprehensive logging and error reporting
- Streamlined API key management through admin panel

## Prerequisites

The automated installation script will handle all dependencies, but if installing manually, ensure you have:

- **PHP 8.3+** with the following extensions:
  - `php-mysql` (MySQL database support)
  - `php-curl` (for VirusTotal API calls)
  - `php-mbstring` (multibyte string support)
  - `php-json` (JSON processing)
  - `php-xml` (XML processing)
- **MySQL 8.0+** or **MariaDB 10.3+**
- **curl** (for API testing)

## Quick Start (Automated Installation)

### Option 1: One-Command Installation (Recommended)

```bash
# Clone the repository
git clone https://github.com/vaishnavucv/Project-secure-ai-virus-scanning-portal.git
cd Project-secure-ai-virus-scanning-portal

# Run the automated installation script
./install.sh

# Or with VirusTotal API key
./install.sh your_virustotal_api_key_here
```

The installation script will automatically:
- ✅ Install all required dependencies (PHP, MySQL, extensions)
- ✅ Setup the database and import schema
- ✅ Fix common compatibility issues (mbstring support)
- ✅ Start the development server
- ✅ Run comprehensive tests
- ✅ Optionally add your VirusTotal API key
- ✅ Verify all functionality is working

**That's it!** The application will be running at `http://127.0.0.1:8000`

### Testing the Installation

After installation, you can run the test script to verify everything is working:

```bash
./test_install.sh
```

### Option 2: Manual Installation

If you prefer manual installation, see the [INSTALLATION.md](INSTALLATION.md) guide.

### Installation Script Benefits

The automated installation script provides:

- **Zero-Configuration Setup**: Handles all dependencies automatically
- **Error Prevention**: Detects and fixes common installation issues
- **Comprehensive Testing**: Verifies all functionality before completion
- **User-Friendly**: Clear progress indicators and error messages
- **Safe Installation**: Won't overwrite existing configurations
- **API Key Integration**: Optionally adds VirusTotal API key during setup
- **Cross-Platform**: Works on various Linux distributions

## Installation & Setup (Manual)

### 1. Clone the Repository

```bash
git clone https://github.com/vaishnavucv/Project-secure-ai-virus-scanning-portal.git
cd Project-secure-ai-virus-scanning-portal
```

### 2. Database Setup

**Note**: The application now includes both MySQL and PostgreSQL schema files for flexibility.

**Option A: Use MySQL (Recommended)**
1. Create a MySQL database and user:
   ```sql
   CREATE DATABASE php_portal;
   CREATE USER 'portal_user'@'localhost' IDENTIFIED BY 'portal_pass';
   GRANT ALL PRIVILEGES ON php_portal.* TO 'portal_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. Import the MySQL schema:
   ```bash
   mysql -u portal_user -p php_portal < schema_mysql.sql
   ```

**Option B: Use PostgreSQL**
1. Install PostgreSQL and modify `db.php` to use PostgreSQL connection
2. Import the schema as-is:
   ```bash
   psql -U postgres -d php_portal -f schema.sql
   ```

### 3. Configuration

Edit `config.php` to match your database credentials:

```php
define('DB_HOST', '127.0.0.1');        // Your database host
define('DB_NAME', 'php_portal');       // Your database name
define('DB_USER', 'portal_user');      // Your database username
define('DB_PASS', 'portal_pass');      // Your database password
```

Alternatively, you can set environment variables:
```bash
export DB_HOST=127.0.0.1
export DB_NAME=php_portal
export DB_USER=portal_user
export DB_PASS=portal_pass
```

### 4. API Keys Setup

1. **VirusTotal API Key**: 
   - Sign up at [VirusTotal](https://www.virustotal.com/gui/join-us)
   - Get your API key from the account settings
   - Add it via the admin panel at `http://127.0.0.1:8000/admin.php?tab=keys`
   - Or add it directly to the database:
     ```sql
     INSERT INTO api_keys (name, api_key, is_active) VALUES ('Main Key', 'your_virustotal_api_key', TRUE);
     ```

2. **OpenAI API Key** (for AI summaries):
   - Sign up at [OpenAI](https://platform.openai.com/)
   - Generate an API key
   - Configure it in the application (check the code for OpenAI integration)

### 5. File Permissions

Ensure the uploads directory is writable:
```bash
chmod 755 uploads/
chown www-data:www-data uploads/  # Adjust user/group as needed
```

## Running the Application

### Development Server

1. Start the PHP built-in server:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

2. Open your browser and navigate to:
   ```
   http://127.0.0.1:8000
   ```

### Production Deployment

For production deployment, consider using:

- **Apache** with mod_php
- **Nginx** with PHP-FPM
- **Docker** containerization

Example Apache virtual host configuration:
```apache
<VirtualHost *:80>
    DocumentRoot /path/to/Project-secure-ai-virus-scanning-portal/public
    ServerName your-domain.com
    
    <Directory /path/to/Project-secure-ai-virus-scanning-portal/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Usage

1. **Login**: Use the demo accounts or create new ones via the admin panel
2. **Upload Files**: Navigate to the upload section and select files for scanning
3. **View Results**: Check scan results and AI-generated summaries
4. **Export Reports**: Download PDF reports of scan results
5. **Admin Functions**: Manage users and API keys (admin only)

## Project Structure

```
Project-secure-ai-virus-scanning-portal/
├── public/                 # Web-accessible files
│   ├── index.php          # Main entry point
│   ├── login.php          # Login page
│   ├── admin.php          # Admin panel
│   ├── user.php           # User dashboard
│   ├── upload.php         # File upload interface
│   ├── scans.php          # Scan history
│   ├── report.php         # Report generation (with mbstring fix)
│   ├── scan_start.php     # File scanning initiation
│   └── scan_status.php    # Scan status checking
├── uploads/               # File upload directory
├── auth.php              # Authentication functions
├── db.php                # Database connection
├── vt.php                # VirusTotal API integration
├── config.php            # Application configuration
├── schema.sql            # Database schema (PostgreSQL)
├── schema_mysql.sql      # Database schema (MySQL)
├── install.sh            # Automated installation script
├── test_install.sh       # Installation testing script
├── INSTALLATION.md       # Detailed installation guide
└── README.md             # This file
```

## Security Notes

- Passwords are hashed using `password_hash()` with bcrypt
- File uploads are validated and stored securely
- API keys are stored in the database (consider encryption for production)
- Session management is implemented for user authentication
- Input validation and sanitization are applied

## Troubleshooting

### Common Issues

1. **Database Connection Error**: Verify database credentials in `config.php`
2. **File Upload Fails**: Check directory permissions for `uploads/`
3. **VirusTotal API Errors**: Verify API key is valid and has sufficient quota
4. **PHP Extensions Missing**: Install required PHP extensions (`php-mysql`, `php-curl`, `php-mbstring`)
5. **mbstring Function Errors**: The application now includes automatic mbstring compatibility fixes

### Quick Fixes

```bash
# Test the installation
./test_install.sh

# Check server status
curl -I http://127.0.0.1:8000

# Check database connection
php -r "require 'db.php'; get_pdo_connection(); echo 'Database OK';"

# Restart the server
pkill -f "php -S 127.0.0.1:8000"
php -S 127.0.0.1:8000 -t public
```

### Logs

Check PHP error logs for debugging:
```bash
tail -f /var/log/php_errors.log
tail -f server.log  # If using the installation script
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions, please open an issue on the GitHub repository.

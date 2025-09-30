# Installation Guide

This guide will help you install and configure the Secure AI Virus Scanning Portal.

## Quick Installation

### Option 1: Automated Installation (Recommended)

Run the automated installation script:

```bash
# Basic installation (without API key)
./install.sh

# Installation with VirusTotal API key
./install.sh your_virustotal_api_key_here
```

### Option 2: Manual Installation

If you prefer to install manually, follow these steps:

1. **Install Dependencies**
   ```bash
   sudo apt update
   sudo apt install -y php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring php8.3-json php8.3-xml mysql-server
   ```

2. **Setup Database**
   ```bash
   sudo systemctl start mysql
   sudo mysql -e "CREATE DATABASE php_portal;"
   sudo mysql -e "CREATE USER 'portal_user'@'localhost' IDENTIFIED BY 'portal_pass';"
   sudo mysql -e "GRANT ALL PRIVILEGES ON php_portal.* TO 'portal_user'@'localhost';"
   sudo mysql -e "FLUSH PRIVILEGES;"
   mysql -u portal_user -p php_portal < schema_mysql.sql
   ```

3. **Start the Application**
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

## What the Installation Script Does

The `install.sh` script automatically:

1. ✅ **Installs Dependencies**
   - PHP 8.3 with required extensions (mysql, curl, mbstring, json, xml)
   - MySQL server
   - curl utility

2. ✅ **Configures Database**
   - Creates MySQL database and user
   - Imports the database schema
   - Seeds demo user accounts

3. ✅ **Fixes Common Issues**
   - Resolves mbstring compatibility issues
   - Sets proper file permissions

4. ✅ **Starts the Server**
   - Launches PHP development server
   - Verifies server is running

5. ✅ **Runs Tests**
   - Tests database connection
   - Tests login functionality
   - Tests admin panel access
   - Tests user dashboard
   - Tests VirusTotal API integration (if API key provided)

6. ✅ **Adds API Key** (optional)
   - Automatically adds VirusTotal API key to database
   - Tests API key functionality

## Testing the Installation

After installation, you can run the test script to verify everything is working:

```bash
./test_install.sh
```

## Accessing the Application

Once installed, access the application at:
- **URL**: http://127.0.0.1:8000
- **Admin**: admin@secure.com / password
- **User**: user1@secure.com / password

## Getting a VirusTotal API Key

1. Go to [VirusTotal](https://www.virustotal.com/gui/join-us)
2. Sign up for a free account
3. Go to your profile settings
4. Copy your API key
5. Add it via the admin panel or during installation

## Troubleshooting

### Common Issues

1. **Permission Denied**
   ```bash
   chmod +x install.sh
   chmod +x test_install.sh
   ```

2. **MySQL Connection Failed**
   ```bash
   sudo systemctl start mysql
   sudo systemctl enable mysql
   ```

3. **PHP Extensions Missing**
   ```bash
   sudo apt install -y php8.3-mysql php8.3-curl php8.3-mbstring
   ```

4. **Port Already in Use**
   ```bash
   pkill -f "php -S 127.0.0.1:8000"
   ```

### Logs

- **Server Log**: `server.log`
- **PHP Errors**: Check system logs or browser developer tools
- **Database Issues**: Check MySQL logs

## Production Deployment

For production deployment, consider:

1. **Web Server**: Use Apache or Nginx instead of PHP built-in server
2. **Database**: Use a dedicated MySQL server
3. **Security**: Configure proper file permissions and SSL
4. **Monitoring**: Set up log monitoring and health checks

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Run the test script: `./test_install.sh`
3. Check the server logs: `tail -f server.log`
4. Open an issue on the GitHub repository

## Script Options

```bash
# Show help
./install.sh --help

# Install without API key
./install.sh

# Install with API key
./install.sh your_api_key_here
```

The installation script is designed to be safe and will not overwrite existing configurations without prompting.

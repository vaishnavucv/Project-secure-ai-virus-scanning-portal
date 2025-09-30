#!/bin/bash

# Secure AI Virus Scanning Portal - Installation & Testing Script
# This script will install dependencies, configure the application, and run tests

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DB_NAME="php_portal"
DB_USER="portal_user"
DB_PASS="portal_pass"
DB_HOST="127.0.0.1"
APP_PORT="8000"
APP_URL="http://127.0.0.1:8000"

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        error "This script should not be run as root. Please run as a regular user."
    fi
}

# Check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Install system dependencies
install_dependencies() {
    log "Installing system dependencies..."
    
    # Update package list
    info "Updating package list..."
    sudo apt update
    
    # Install PHP and required extensions
    info "Installing PHP and extensions..."
    sudo apt install -y php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring php8.3-json php8.3-xml
    
    # Install MySQL
    if ! command_exists mysql; then
        info "Installing MySQL..."
        sudo apt install -y mysql-server
        sudo systemctl start mysql
        sudo systemctl enable mysql
    else
        info "MySQL is already installed"
    fi
    
    # Install curl if not present
    if ! command_exists curl; then
        info "Installing curl..."
        sudo apt install -y curl
    fi
    
    log "Dependencies installed successfully!"
}

# Check PHP extensions
check_php_extensions() {
    log "Checking PHP extensions..."
    
    local required_extensions=("mysql" "curl" "mbstring" "json" "xml")
    local missing_extensions=()
    
    for ext in "${required_extensions[@]}"; do
        if ! php -m | grep -q "^$ext$"; then
            missing_extensions+=("$ext")
        fi
    done
    
    if [ ${#missing_extensions[@]} -ne 0 ]; then
        error "Missing PHP extensions: ${missing_extensions[*]}. Please install them and run this script again."
    fi
    
    log "All required PHP extensions are installed!"
}

# Setup MySQL database
setup_database() {
    log "Setting up MySQL database..."
    
    # Check if MySQL is running
    if ! sudo systemctl is-active --quiet mysql; then
        info "Starting MySQL service..."
        sudo systemctl start mysql
    fi
    
    # Create database and user
    info "Creating database and user..."
    sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
    sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
    sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
    sudo mysql -e "FLUSH PRIVILEGES;"
    
    # Import schema
    info "Importing database schema..."
    if [ -f "schema_mysql.sql" ]; then
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < schema_mysql.sql
    else
        error "schema_mysql.sql not found!"
    fi
    
    log "Database setup completed!"
}

# Test database connection
test_database() {
    log "Testing database connection..."
    
    # Create test script
    cat > test_db_connection.php << 'EOF'
<?php
require_once __DIR__ . '/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "SUCCESS: Database connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "SUCCESS: Found {$result['count']} users in database\n";
    
} catch (PDOException $e) {
    echo "ERROR: Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
EOF
    
    # Run test
    if php test_db_connection.php; then
        log "Database connection test passed!"
        rm -f test_db_connection.php
    else
        error "Database connection test failed!"
    fi
}

# Start PHP development server
start_server() {
    log "Starting PHP development server..."
    
    # Kill any existing server on the port
    if lsof -Pi :$APP_PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
        info "Stopping existing server on port $APP_PORT..."
        pkill -f "php -S 127.0.0.1:$APP_PORT" || true
        sleep 2
    fi
    
    # Start server in background
    info "Starting server on $APP_URL..."
    php -S 127.0.0.1:$APP_PORT -t public > server.log 2>&1 &
    SERVER_PID=$!
    
    # Wait for server to start
    sleep 3
    
    # Check if server is running
    if ! curl -s -o /dev/null -w "%{http_code}" "$APP_URL" | grep -q "200\|302"; then
        error "Failed to start PHP server!"
    fi
    
    log "PHP server started successfully on $APP_URL (PID: $SERVER_PID)"
}

# Test application functionality
test_application() {
    log "Testing application functionality..."
    
    # Test 1: Check if server responds
    info "Test 1: Checking server response..."
    if curl -s -o /dev/null -w "%{http_code}" "$APP_URL" | grep -q "200\|302"; then
        log "✓ Server is responding"
    else
        error "✗ Server is not responding"
    fi
    
    # Test 2: Check login page
    info "Test 2: Checking login page..."
    if curl -s "$APP_URL/login.php" | grep -q "Sign in"; then
        log "✓ Login page loads correctly"
    else
        error "✗ Login page failed to load"
    fi
    
    # Test 3: Test login functionality
    info "Test 3: Testing login functionality..."
    if curl -s -c cookies.txt -X POST -d "email=admin@secure.com&password=password" "$APP_URL/login.php" | grep -q "302\|Location"; then
        log "✓ Admin login works"
    else
        error "✗ Admin login failed"
    fi
    
    # Test 4: Test admin panel access
    info "Test 4: Testing admin panel access..."
    if curl -s -b cookies.txt "$APP_URL/admin.php" | grep -q "Admin Panel"; then
        log "✓ Admin panel accessible"
    else
        error "✗ Admin panel not accessible"
    fi
    
    # Test 5: Test user login
    info "Test 5: Testing user login..."
    if curl -s -c cookies_user.txt -X POST -d "email=user1@secure.com&password=password" "$APP_URL/login.php" | grep -q "302\|Location"; then
        log "✓ User login works"
    else
        error "✗ User login failed"
    fi
    
    # Test 6: Test user dashboard
    info "Test 6: Testing user dashboard..."
    if curl -s -b cookies_user.txt "$APP_URL/user.php" | grep -q "Dashboard\|Upload"; then
        log "✓ User dashboard accessible"
    else
        error "✗ User dashboard not accessible"
    fi
    
    # Clean up test files
    rm -f cookies.txt cookies_user.txt
    
    log "All application tests passed!"
}

# Add VirusTotal API key
add_api_key() {
    log "Adding VirusTotal API key..."
    
    # Check if API key is provided
    if [ -z "$VIRUSTOTAL_API_KEY" ]; then
        warning "No VirusTotal API key provided. You can add it later via the admin panel."
        return 0
    fi
    
    # Add API key to database
    cat > add_api_key.php << EOF
<?php
require_once __DIR__ . '/db.php';

try {
    \$pdo = get_pdo_connection();
    \$stmt = \$pdo->prepare('INSERT INTO api_keys (name, api_key, is_active) VALUES (?, ?, ?)');
    \$result = \$stmt->execute(['Main Key', '$VIRUSTOTAL_API_KEY', true]);
    
    if (\$result) {
        echo "SUCCESS: API key added successfully!\n";
    } else {
        echo "ERROR: Failed to add API key\n";
        exit(1);
    }
} catch (Exception \$e) {
    echo "ERROR: " . \$e->getMessage() . "\n";
    exit(1);
}
EOF
    
    if php add_api_key.php; then
        log "VirusTotal API key added successfully!"
        rm -f add_api_key.php
    else
        error "Failed to add VirusTotal API key!"
    fi
}

# Test VirusTotal API
test_virustotal_api() {
    log "Testing VirusTotal API integration..."
    
    cat > test_vt_api.php << 'EOF'
<?php
require_once __DIR__ . '/vt.php';

if (!vt_curl_available()) {
    echo "ERROR: cURL is not available\n";
    exit(1);
}

$apiKey = vt_get_active_api_key();
if (!$apiKey) {
    echo "WARNING: No active API key found\n";
    exit(0);
}

echo "SUCCESS: Active API key found: " . substr($apiKey, 0, 10) . "...\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.virustotal.com/api/v3/domains/google.com',
    CURLOPT_HTTPHEADER => ['x-apikey: ' . $apiKey],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "SUCCESS: VirusTotal API is working correctly!\n";
} elseif ($httpCode === 401) {
    echo "ERROR: API key is invalid or unauthorized\n";
    exit(1);
} elseif ($httpCode === 403) {
    echo "ERROR: API key is valid but doesn't have permission\n";
    exit(1);
} elseif ($httpCode === 429) {
    echo "WARNING: Rate limit exceeded - API key is working but too many requests\n";
} else {
    echo "ERROR: API connection failed (HTTP $httpCode)\n";
    exit(1);
}
EOF
    
    if php test_vt_api.php; then
        log "VirusTotal API test passed!"
        rm -f test_vt_api.php
    else
        warning "VirusTotal API test failed or no API key configured"
        rm -f test_vt_api.php
    fi
}

# Fix mbstring issue in report.php
fix_mbstring_issue() {
    log "Fixing mbstring issue in report.php..."
    
    if [ -f "public/report.php" ]; then
        # Replace mb_substr with substr if mbstring is not available
        if ! php -m | grep -q "^mbstring$"; then
            info "mbstring not available, replacing mb_substr with substr..."
            sed -i 's/mb_substr/substr/g' public/report.php
            log "Fixed mbstring issue in report.php"
        else
            info "mbstring is available, no fix needed"
        fi
    fi
}

# Display final information
show_final_info() {
    log "Installation completed successfully!"
    echo
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}  Secure AI Virus Scanning Portal${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo
    echo -e "${BLUE}Application URL:${NC} $APP_URL"
    echo -e "${BLUE}Server PID:${NC} $SERVER_PID"
    echo -e "${BLUE}Server Log:${NC} server.log"
    echo
    echo -e "${YELLOW}Demo Accounts:${NC}"
    echo -e "  Admin: admin@secure.com / password"
    echo -e "  User:  user1@secure.com / password"
    echo
    echo -e "${YELLOW}Next Steps:${NC}"
    echo -e "  1. Open $APP_URL in your browser"
    echo -e "  2. Login with demo accounts"
    echo -e "  3. Add your VirusTotal API key via admin panel"
    echo -e "  4. Start uploading files for scanning"
    echo
    echo -e "${YELLOW}To stop the server:${NC}"
    echo -e "  kill $SERVER_PID"
    echo
    echo -e "${YELLOW}To restart the server:${NC}"
    echo -e "  php -S 127.0.0.1:$APP_PORT -t public"
    echo
}

# Cleanup function
cleanup() {
    if [ ! -z "$SERVER_PID" ]; then
        info "Stopping PHP server..."
        kill $SERVER_PID 2>/dev/null || true
    fi
    rm -f test_db_connection.php add_api_key.php test_vt_api.php
}

# Set trap for cleanup
trap cleanup EXIT

# Main installation process
main() {
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}  Secure AI Virus Scanning Portal${NC}"
    echo -e "${GREEN}  Installation & Testing Script${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo
    
    # Check if running as root
    check_root
    
    # Install dependencies
    install_dependencies
    
    # Check PHP extensions
    check_php_extensions
    
    # Setup database
    setup_database
    
    # Test database
    test_database
    
    # Fix mbstring issue
    fix_mbstring_issue
    
    # Start server
    start_server
    
    # Test application
    test_application
    
    # Add API key if provided
    add_api_key
    
    # Test VirusTotal API
    test_virustotal_api
    
    # Show final information
    show_final_info
}

# Check for command line arguments
if [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
    echo "Usage: $0 [VIRUSTOTAL_API_KEY]"
    echo
    echo "This script will:"
    echo "  1. Install all required dependencies"
    echo "  2. Setup MySQL database"
    echo "  3. Start the PHP development server"
    echo "  4. Run comprehensive tests"
    echo "  5. Optionally add a VirusTotal API key"
    echo
    echo "Examples:"
    echo "  $0                                    # Install without API key"
    echo "  $0 your_virustotal_api_key_here      # Install with API key"
    echo
    exit 0
fi

# Set VirusTotal API key if provided
if [ ! -z "$1" ]; then
    VIRUSTOTAL_API_KEY="$1"
fi

# Run main installation
main

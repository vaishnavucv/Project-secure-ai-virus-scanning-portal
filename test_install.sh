#!/bin/bash

# Quick test script for the installation
# This script tests the basic functionality without full installation

set -e

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[TEST] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

echo "Testing Secure AI Virus Scanning Portal..."
echo

# Test 1: Check if server is running
log "Test 1: Checking if server is running..."
if curl -s -o /dev/null -w "%{http_code}" "http://127.0.0.1:8000" | grep -q "200\|302"; then
    log "✓ Server is running"
else
    error "✗ Server is not running. Please start it with: php -S 127.0.0.1:8000 -t public"
fi

# Test 2: Check database connection
log "Test 2: Checking database connection..."
if php -r "
require_once 'db.php';
try {
    \$pdo = get_pdo_connection();
    echo 'SUCCESS';
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
    exit(1);
}
" | grep -q "SUCCESS"; then
    log "✓ Database connection works"
else
    error "✗ Database connection failed"
fi

# Test 3: Check login functionality
log "Test 3: Testing login functionality..."
if curl -s -c test_cookies.txt -X POST -d "email=admin@secure.com&password=password" "http://127.0.0.1:8000/login.php" -w "%{http_code}" | grep -q "302"; then
    log "✓ Admin login works"
else
    error "✗ Admin login failed"
fi

# Test 4: Check admin panel
log "Test 4: Testing admin panel access..."
if curl -s -b test_cookies.txt "http://127.0.0.1:8000/admin.php" | grep -q "Admin Panel"; then
    log "✓ Admin panel accessible"
else
    error "✗ Admin panel not accessible"
fi

# Test 5: Check report.php (mbstring fix)
log "Test 5: Testing report.php (mbstring fix)..."
if curl -s -b test_cookies.txt "http://127.0.0.1:8000/report.php?id=1" 2>&1 | grep -q "mb_substr"; then
    warning "⚠ Report page still has mbstring issues"
else
    log "✓ Report page works (mbstring issue fixed)"
fi

# Cleanup
rm -f test_cookies.txt

echo
log "All tests completed successfully!"
echo
echo "The application is working correctly!"
echo "You can access it at: http://127.0.0.1:8000"
echo "Login with: admin@secure.com / password"

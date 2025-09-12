<?php
// Basic configuration for database connection

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'php_portal');
define('DB_USER', getenv('DB_USER') ?: 'portal_user');
define('DB_PASS', getenv('DB_PASS') ?: 'portal_pass');

define('APP_NAME', 'PHP Web Portal');
define('APP_BASE_URL', getenv('APP_BASE_URL') ?: '/');



<?php
// Basic configuration for database connection

define('DB_HOST', getenv('PGHOST') ?: '127.0.0.1');
define('DB_NAME', getenv('PGDATABASE') ?: 'php_portal');
define('DB_USER', getenv('PGUSER') ?: 'portal_user');
define('DB_PASS', getenv('PGPASSWORD') ?: 'portal_pass');

define('APP_NAME', 'PHP Web Portal');
define('APP_BASE_URL', getenv('APP_BASE_URL') ?: '/');



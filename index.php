<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/**
 * Dual-Environment Subdirectory & Shared Hosting Front Controller
 * Works seamlessly on:
 *  - Local Apache/Nginx: http://localhost/register/
 *  - Local Artisan Serve: http://localhost:8000/
 *  - InfinityFree Subdirectory: https://erigester.freedev.app/register/
 *  - Root Domain Hosting: https://erigester.freedev.app/
 */

// Normalize SCRIPT_NAME and PHP_SELF when routed via root index.php instead of public/index.php
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $baseSubdir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $_SERVER['SCRIPT_NAME'] = $baseSubdir.'/index.php';
    $_SERVER['PHP_SELF'] = $baseSubdir.'/index.php';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());

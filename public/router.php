<?php

declare(strict_types=1);

// Let PHP's development server serve existing static assets itself. All other
// requests go through Symfony's front controller.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__.($path ?: '/');

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__.'/index.php';

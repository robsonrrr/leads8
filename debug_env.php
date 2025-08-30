<?php

// Initialize Kohana framework constants
define('EXT', '.php');
define('DOCROOT', '/var/www/html/');
define('APPPATH', '/var/www/html/application/');
define('MODPATH', '/var/www/html/modules/');
define('SYSPATH', '/var/www/html/vendor/koseven/koseven/system/');

// Include the bootstrap to load environment variables
require_once APPPATH.'bootstrap'.EXT;

echo "Environment Variables Debug:\n";
echo "api_vallery_v1: " . var_export($_ENV['api_vallery_v1'], true) . "\n";
echo "auth: " . var_export($_ENV['auth'], true) . "\n";
echo "AUTH constant defined: " . (defined('AUTH') ? 'yes' : 'no') . "\n";
if (defined('AUTH')) {
    echo "AUTH value: " . AUTH . "\n";
}

// Test the GraphQL URL construction
if (isset($_ENV['api_vallery_v1'])) {
    $url = $_ENV['api_vallery_v1'] . "/gql/?query=";
    echo "GraphQL URL: " . $url . "\n";
}
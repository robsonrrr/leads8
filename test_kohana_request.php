<?php

// Test Kohana Request class
echo "Testing Kohana Request class...\n";

// Initialize minimal Kohana constants
define('EXT', '.php');
define('DOCROOT', '/var/www/html/');
define('APPPATH', '/var/www/html/application/');
define('MODPATH', '/var/www/html/modules/');
define('SYSPATH', '/var/www/html/vendor/koseven/koseven/system/');

// Include Kohana core
require_once SYSPATH.'classes/Kohana/Core'.EXT;
require_once SYSPATH.'classes/Kohana'.EXT;

// Initialize Kohana
Kohana::init(array(
    'base_url' => '/',
    'index_file' => FALSE,
));

// Load environment variables
$Loader = new josegonzalez\Dotenv\Loader('/data/config.env');
$Loader->parse();
$Loader->toEnv();

// Define AUTH constant
define('AUTH', base64_encode($_ENV['auth']));

echo "Environment loaded. AUTH: " . AUTH . "\n";
echo "API URL: " . $_ENV['api_vallery_v1'] . "\n";

// Test the same request as the controller
$url = $_ENV['api_vallery_v1'] . '/gql/?query=';
$uri = sprintf('{Lead(id: %s) { id dataEmissao clientePOID }}', 637672);

echo "Full URL: " . $url . urlencode($uri) . "\n";

try {
    $response = Request::factory($url . urlencode($uri))
        ->headers(array('Authorization' => 'Basic ' . AUTH))
        ->method('GET')
        ->execute()
        ->body();
    
    echo "Response length: " . strlen($response) . "\n";
    echo "Response: " . $response . "\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
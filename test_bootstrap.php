<?php

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test script to debug bootstrap loading

echo "--- Starting Bootstrap Debug ---\n\n";

// Define basic constants from index.php
define('EXT', '.php');
define('DOCROOT', realpath(__DIR__).DIRECTORY_SEPARATOR);
define('APPPATH', realpath(DOCROOT.'application').DIRECTORY_SEPARATOR);
define('MODPATH', realpath(DOCROOT.'modules').DIRECTORY_SEPARATOR);
define('SYSPATH', realpath(DOCROOT.'vendor/koseven/koseven/system').DIRECTORY_SEPARATOR);

echo "DOCROOT: " . DOCROOT . "\n";
echo "APPPATH: " . APPPATH . "\n";
echo "MODPATH: " . MODPATH . "\n";
echo "SYSPATH: " . SYSPATH . "\n\n";

// --- Attempt to load core files ---

$core_path = SYSPATH.'classes/KO7/Core'.EXT;
echo "Attempting to load KO7_Core: $core_path\n";
if (file_exists($core_path)) {
    require $core_path;
    echo "KO7_Core loaded successfully.\n";
} else {
    echo "ERROR: KO7_Core not found.\n";
}

$kohana_core_path = MODPATH.'kohana/classes/Kohana/Core'.EXT;
echo "Attempting to load Kohana_Core: $kohana_core_path\n";
if (file_exists($kohana_core_path)) {
    require $kohana_core_path;
    echo "Kohana_Core loaded successfully.\n";
} else {
    echo "ERROR: Kohana_Core not found.\n";
}

$kohana_path = MODPATH.'kohana/classes/Kohana'.EXT;
echo "Attempting to load Kohana: $kohana_path\n";
if (file_exists($kohana_path)) {
    require $kohana_path;
    echo "Kohana class file loaded successfully.\n";
} else {
    echo "ERROR: Kohana class file not found.\n";
}

// Check if classes exist
echo "\n--- Verifying Class Definitions ---\n";
echo 'class_exists("KO7_Core"): ' . (class_exists('KO7_Core') ? 'Yes' : 'No') . "\n";
echo 'class_exists("Kohana_Core"): ' . (class_exists('Kohana_Core') ? 'Yes' : 'No') . "\n";
echo 'class_exists("Kohana"): ' . (class_exists('Kohana') ? 'Yes' : 'No') . "\n";


echo "\n--- Finished Bootstrap Debug ---\n";

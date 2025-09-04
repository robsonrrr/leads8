<?php

// -- Environment setup --------------------------------------------------------


/**
 * Set the default time zone.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set('America/Chicago');

/**
 * Set the default locale.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'pt_BR.utf-8');

/**
 * Enable the KO7 auto-loader.
 *
 * @link http://kohanaframework.org/guide/using.autoloading
 * @link http://www.php.net/manual/function.spl-autoload-register
 */
// Load the core KO7 class (Koseven framework)
require SYSPATH.'classes/KO7/Core'.EXT;

if (is_file(APPPATH.'classes/KO7'.EXT))
{
    // Application extends the core
    require APPPATH.'classes/KO7'.EXT;
}
else
{
    // Load empty core extension
    require SYSPATH.'classes/KO7'.EXT;
}

spl_autoload_register(['KO7', 'auto_load']);

/**
 * Optionally, you can enable a compatibility auto-loader for use with
 * older modules that have not been updated for PSR-0.
 *
 * It is recommended to not enable this unless absolutely necessary.
 */
//spl_autoload_register(array('Kohana', 'auto_load_lowercase'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @link http://www.php.net/manual/function.spl-autoload-call
 * @link http://www.php.net/manual/var.configuration#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

/**
 * Enable composer autoload libraries
 */
require DOCROOT . '/vendor/autoload.php';

/**
 * Set the mb_substitute_character to "none"
 *
 * @link http://www.php.net/manual/function.mb-substitute-character.php
 */
mb_substitute_character('none');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('pt-br');

if (isset($_SERVER['SERVER_PROTOCOL']))
{
    // Replace the default protocol.
    HTTP::$protocol = $_SERVER['SERVER_PROTOCOL'];
}

/**
 * Set KO7::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant KO7::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
{
    KO7::$environment = constant('KO7::'.strtoupper($_SERVER['KOHANA_ENV']));
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php", if set to FALSE uses clean URLS     index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  expose      set the X-Powered-By header                        FALSE
 */
KO7::init([
    'base_url'   => '/leads8/',
    'index_file' => FALSE,
]);

/**
 * Custom error handler to suppress PHP 8+ setcookie deprecation warnings
 * This must be set after KO7::init() to override KO7's error handler
 */
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Suppress setcookie null value deprecation warnings
    if ($errno === E_DEPRECATED && strpos($errstr, 'setcookie()') !== false && strpos($errstr, 'Passing null to parameter') !== false) {
        return true; // Suppress this specific warning
    }
    // Let other errors be handled normally by KO7
    return KO7_Core::error_handler($errno, $errstr, $errfile, $errline);
}, E_DEPRECATED);

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
KO7::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
KO7::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
KO7::modules([
    // 'encrypt'    => MODPATH.'encrypt',    // Encryption supprt
    // 'auth'       => MODPATH.'auth',       // Basic authentication
    'cache'      => MODPATH.'cache',      // Caching with multiple backends
    // 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
    'database'   => MODPATH.'database',   // Database access
     'mobile'   	=> MODPATH.'mobile',	 // Mobile Detect https://github.com/serbanghita/Mobile-Detect/
    // 'image'      => MODPATH.'image',      // Image manipulation
    // 'minion'     => MODPATH.'minion',     // CLI Tasks
    // 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
    // 'pagination' => MODPATH.'pagination', // Pagination
    // 'unittest'   => MODPATH.'unittest',   // Unit testing
    // 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
    'mysqli'     => MODPATH.'MySQLi',     // Database access	
]);



/**
 * Cookie Salt
 * @see  http://kohanaframework.org/3.3/guide/kohana/cookies
 *
 * If you have not defined a cookie salt in your Cookie class then
 * uncomment the line below and define a preferrably long salt.
 */
 Cookie::$salt = 'CRM(*@*&)(AS)AS(*S*A)';
/**
 * Cookie HttpOnly directive
 * If set to true, disallows cookies to be accessed from JavaScript
 * @see https://en.wikipedia.org/wiki/Session_hijacking
 */
Cookie::$httponly = TRUE;
/**
 * If website runs on secure protocol HTTPS, allows cookies only to be transmitted
 * via HTTPS.
 * Warning: HSTS must also be enabled in .htaccess, otherwise first request
 * to http://www.example.com will still reveal this cookie
 */
Cookie::$secure = isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on' ? TRUE : FALSE;


$Loader = new josegonzalez\Dotenv\Loader('/data/config.env');
// Parse the .env file
$Loader->parse();
// Send the parsed .env file to the $_ENV variable
$Loader->toEnv();

define('SOLR', $_ENV['solr']);
define('AUTH', base64_encode($_ENV['auth']));
define('AUTH2', base64_encode($_ENV['auth']));

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
Route::set('helper', 'helper(/<action>(/<id>))')
    ->defaults([
        'controller' => 'helper',
        'action'     => 'index',
    ]);

Route::set('service', 'service(/<action>(/<id>))')
    ->defaults([
        'controller' => 'service',
        'action'     => 'index',
    ]);

Route::set('bootcomplete', 'search/bootcomplete(/<id>)')
    ->defaults([
        'directory'  => 'lead',
        'controller' => 'search',
        'action'     => 'bootcomplete',
    ]);

Route::set('lead', 'lead(/<controller>(/<id>(/<segment>(/<complement>))))')
    ->defaults([
        'directory'  => 'lead',
        'controller' => 'index',
        'action'     => 'index',
    ]);

Route::set('ticket', 'ticket(/<controller>(/<id>(/<segment>)))')
    ->defaults([
        'directory'  => 'ticket',
        'controller' => 'index',
        'action'     => 'index',
    ]);

Route::set('order', 'order(/<controller>(/<id>(/<segment>)))')
    ->defaults([
        'directory'  => 'order',
        'controller' => 'index',
        'action'     => 'index',
    ]);

Route::set('product', 'product(/<controller>(/<id>(/<id2>)))')
    ->defaults([
        'directory'  => 'product',
        'controller' => 'index',
        'action'     => 'index',
    ]);

Route::set('dashboard', 'dashboard(/<action>(/<id>))')
    ->defaults([
        'controller' => 'Dashboard',
        'action'     => 'index',
    ]);

Route::set('segment', 'segment(/<id>)')
    ->defaults([
        'directory'  => 'lead',
        'controller' => 'segment',
        'action'     => 'index',
    ]);

Route::set('leads8', 'leads8(/<controller>(/<id>(/<segment>(/<complement>))))')
    ->defaults([
        'directory'  => 'lead',
        'controller' => 'index',
        'action'     => 'index',
    ]);

Route::set('default', '<id>(/<segment>)')
    ->defaults([
        'directory'  => 'lead',
        'controller' => 'index',
        'action'     => 'index',
    ]);
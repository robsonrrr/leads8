<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Mobile API Configuration
|--------------------------------------------------------------------------
|
| Este arquivo contém as configurações específicas para a API mobile
|
*/

// Versão atual da API
$config['api_version'] = '1.0.0';

// Versão mínima suportada do app
$config['min_app_version'] = '1.0.0';

// Tempo de expiração do token (em segundos)
$config['token_expiration'] = 30 * 24 * 60 * 60; // 30 dias

// Limites de paginação
$config['pagination'] = [
    'default_limit' => 20,
    'max_limit' => 100
];

// Configurações de cache
$config['cache'] = [
    'products' => 3600, // 1 hora
    'categories' => 86400, // 24 horas
    'settings' => 86400 // 24 horas
];

// Configurações de upload
$config['upload'] = [
    'max_size' => 5120, // 5MB
    'allowed_types' => 'jpg|jpeg|png',
    'max_width' => 2048,
    'max_height' => 2048
];

// Configurações de notificação
$config['notifications'] = [
    'enabled' => true,
    'provider' => 'firebase',
    'firebase_key' => 'YOUR_FIREBASE_KEY'
];

// Features habilitadas
$config['features'] = [
    'offline_mode' => true,
    'biometric_auth' => true,
    'push_notifications' => true,
    'location_services' => true,
    'file_upload' => true
];

// Endpoints permitidos sem autenticação
$config['public_endpoints'] = [
    'mobile/auth',
    'mobile/index',
    'mobile/version'
];

// Configurações de segurança
$config['security'] = [
    'enable_rate_limiting' => true,
    'rate_limit_requests' => 100, // requisições
    'rate_limit_window' => 3600, // 1 hora
    'enable_ip_whitelist' => false,
    'ip_whitelist' => []
];

// Configurações de log
$config['logging'] = [
    'enabled' => true,
    'level' => 'ERROR', // DEBUG, INFO, WARNING, ERROR
    'file' => APPPATH . 'logs/mobile.log'
];

// Configurações de resposta
$config['response'] = [
    'show_execution_time' => false,
    'show_memory_usage' => false,
    'pretty_print' => false
];

// Configurações de compressão
$config['compression'] = [
    'enabled' => true,
    'level' => 6 // 0-9
];

// Configurações de CORS
$config['cors'] = [
    'enabled' => true,
    'allowed_origins' => ['*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false
];

// Configurações de cache de resposta
$config['response_cache'] = [
    'enabled' => true,
    'driver' => 'file', // file, redis, memcached
    'key_prefix' => 'mobile_api_',
    'ttl' => 3600 // 1 hora
];

// Configurações de timeout
$config['timeouts'] = [
    'default' => 30, // segundos
    'upload' => 300, // 5 minutos
    'download' => 300 // 5 minutos
];

// Configurações de retry
$config['retry'] = [
    'enabled' => true,
    'max_attempts' => 3,
    'delay' => 1000, // milisegundos
    'multiplier' => 2 // delay exponencial
];

// Configurações de fallback
$config['fallback'] = [
    'enabled' => true,
    'cache_ttl' => 86400, // 24 horas
    'max_items' => 1000
];

// Configurações de sincronização
$config['sync'] = [
    'batch_size' => 100,
    'max_concurrent' => 3,
    'timeout' => 300 // 5 minutos
];

// Configurações de métricas
$config['metrics'] = [
    'enabled' => true,
    'provider' => 'prometheus',
    'endpoint' => '/metrics',
    'namespace' => 'mobile_api'
];

// Configurações de debug
$config['debug'] = [
    'enabled' => false,
    'show_queries' => false,
    'show_routes' => false
];

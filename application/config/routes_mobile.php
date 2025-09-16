<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
|
| Este arquivo contém as rotas específicas para a API mobile
|
*/

// Rotas públicas
$route['api/v1'] = 'mobile/index';
$route['api/v1/auth'] = 'mobile/auth';
$route['api/v1/version'] = 'mobile/version';

// Rotas autenticadas
$route['api/v1/leads'] = 'mobile/leads';
$route['api/v1/leads/(:num)'] = 'mobile/lead/$1';
$route['api/v1/leads/create'] = 'mobile/create_lead';
$route['api/v1/leads/update/(:num)'] = 'mobile/update_lead/$1';
$route['api/v1/leads/delete/(:num)'] = 'mobile/delete_lead/$1';

$route['api/v1/products'] = 'mobile/products';
$route['api/v1/products/(:num)'] = 'mobile/product/$1';
$route['api/v1/products/search'] = 'mobile/search_products';
$route['api/v1/products/categories'] = 'mobile/product_categories';

$route['api/v1/cart'] = 'mobile/cart';
$route['api/v1/cart/add'] = 'mobile/add_to_cart';
$route['api/v1/cart/update'] = 'mobile/update_cart';
$route['api/v1/cart/remove'] = 'mobile/remove_from_cart';
$route['api/v1/cart/clear'] = 'mobile/clear_cart';

$route['api/v1/customers'] = 'mobile/customers';
$route['api/v1/customers/(:num)'] = 'mobile/customer/$1';
$route['api/v1/customers/search'] = 'mobile/search_customers';

$route['api/v1/profile'] = 'mobile/profile';
$route['api/v1/profile/update'] = 'mobile/update_profile';
$route['api/v1/profile/password'] = 'mobile/update_password';

$route['api/v1/settings'] = 'mobile/settings';
$route['api/v1/sync'] = 'mobile/sync';
$route['api/v1/notifications'] = 'mobile/notifications';

// Rotas de upload
$route['api/v1/upload/image'] = 'mobile/upload_image';
$route['api/v1/upload/document'] = 'mobile/upload_document';

// Rotas de relatórios
$route['api/v1/reports/sales'] = 'mobile/sales_report';
$route['api/v1/reports/performance'] = 'mobile/performance_report';
$route['api/v1/reports/stock'] = 'mobile/stock_report';

// Rotas de métricas e saúde
$route['api/v1/health'] = 'mobile/health';
$route['api/v1/metrics'] = 'mobile/metrics';

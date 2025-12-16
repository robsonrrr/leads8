<?php

/**
 * Route Configuration
 * 
 * This file contains all route definitions for the application.
 * Routes are processed in the order they are defined.
 */

// Admin de Tickets - deve vir ANTES da rota default
Route::set('csuite', 'csuite(/<action>(/<id>))', [
    'action' => '(index|edit|view|updateStatus)',
    'id' => '([0-9]+)?'
])
->defaults([
    'directory'  => 'csuite',
    'controller' => 'index',
    'action'     => 'index',
]);


// Admin de Tickets - deve vir ANTES da rota default
Route::set('tickets', 'tickets(/<action>(/<id>))', [
    'action' => '(index|edit|delete|updateStatus)',
    'id' => '([0-9]+)?'
])
->defaults([
    'controller' => 'tickets',
    'action'     => 'index',
]);

// Rota para o admin de variáveis deve vir antes da rota default
Route::set('vars', 'vars(/<action>(/<id>))', [
    'action' => '(index|edit|view|update_field)',
    'id' => '([0-9]+)?'
])
->defaults([
    'controller' => 'vars',
    'action'     => 'index',
]);

Route::set('customer', 'customer(/<action>(/<id>))', [
    'action' => '(index|detail|ajax|api|monthly_sales)',
    'id' => '([0-9]+)?'
])
->defaults([
    'controller' => 'customer',
    'action'     => 'index',
]);

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

// API test route
Route::set('api_test', 'api/test')
    ->defaults([
        'directory'  => 'api',
        'controller' => 'test',
        'action'     => 'index',
    ]);

// API routes for price history
Route::set('api_pricehistory_test', 'api/pricehistory')
    ->defaults([
        'directory'  => 'api',
        'controller' => 'pricehistory',
        'action'     => 'index',
    ]);

Route::set('api_pricehistory_product', 'api/pricehistory/product(/<id>)')
    ->defaults([
        'directory'  => 'api',
        'controller' => 'pricehistory',
        'action'     => 'product',
    ]);

Route::set('api_pricehistory_lastprice', 'api/pricehistory/lastprice(/<id>)')
    ->defaults([
        'directory'  => 'api',
        'controller' => 'pricehistory',
        'action'     => 'lastprice',
    ]);

Route::set('api_pricehistory_customer', 'api/pricehistory/customer(/<id>)')
    ->defaults([
        'directory'  => 'api',
        'controller' => 'pricehistory',
        'action'     => 'customer',
    ]);

Route::set('api_pricehistory_compare', 'api/pricehistory/compare(/<id>)')
    ->defaults([
        'directory'  => 'api',
        'controller' => 'pricehistory',
        'action'     => 'compare',
    ]);

Route::set('api_pricehistory_trends', 'api/pricehistory/trends(/<id>)')
    ->defaults([
        'directory'  => 'api',
        'controller' => 'pricehistory',
        'action'     => 'trends',
    ]);

Route::set('api_pricehistory_bulk', 'api/pricehistory/bulk-import')
    ->defaults([
        'directory'  => 'api',
        'controller' => 'pricehistory',
        'action'     => 'bulk_import',
    ]);

Route::set('bootcomplete', 'search/bootcomplete(/<id>)')
    ->defaults([
        'directory'  => 'lead',
        'controller' => 'search',
        'action'     => 'bootcomplete',
    ]);

Route::set('lead_get', 'lead/get(/<id>(/<segment>))')
    ->defaults([
        'directory'  => 'lead',
        'controller' => 'get',
        'action'     => 'index',
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

Route::set('sales', 'sales(/<controller>(/<action>(/<id>)))')
    ->defaults([
        'directory'  => 'sales',
        'controller' => 'history',
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

Route::set('lead_show', 'lead/show(/<id>(/<segment>))')
    ->defaults(['directory' => 'Lead', 'controller' => 'Show', 'action' => 'index']);


// Editor de Views SQL - deve vir ANTES da rota default
Route::set('viewsql_actions', 'viewsql(/<action>(/<id>))', ['id' => '.*'])
    ->defaults([
        'controller' => 'viewsql',
        'action'     => 'index',
    ]);

// Vars controller - deve vir ANTES da rota default
Route::set('vars_edit_field', 'vars/edit_field/<id>/<field>')
    ->defaults([
        'controller' => 'vars',
        'action'     => 'edit_field',
    ]);

Route::set('vars_actions', 'vars(/<action>(/<id>))')
    ->defaults([
        'controller' => 'vars',
        'action'     => 'index',
    ]);

// VarsSimple controller - deve vir ANTES da rota default
Route::set('vars_simple', 'vars_simple(/<action>(/<id>))')
    ->defaults([
        'controller' => 'VarsSimple',
        'action'     => 'index',
    ]);


// Default route - only match when there's actually an ID
Route::set('default', '<id>(/<segment>)', ['id' => '[0-9]+'])
    ->defaults([
        'directory'  => 'lead',
        'controller' => 'index',
        'action'     => 'index',
    ]);
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/

// Hook para autenticação da API mobile
$hook['pre_controller'][] = array(
    'class'    => 'Mobile_auth',
    'function' => 'check_auth',
    'filename' => 'Mobile_auth.php',
    'filepath' => 'hooks'
);

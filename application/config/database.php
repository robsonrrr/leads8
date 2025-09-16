
<?php

return [
    'default' => [
        'type'       => 'MySQLi',
        'connection' => [
            /**
			 * MySQLi driver config information
			 *
			 * The following options are available for MySQLi:
			 *
			 * string   hostname     server hostname, or socket
			 * string   database     database name
			 * string   username     database username
			 * string   password     database password
			 * boolean  persistent   use persistent connections?
			 * array    ssl          ssl parameters as "key => value" pairs.
			 *                       Available keys: client_key_path, client_cert_path, ca_cert_path, ca_dir_path, cipher
			 * array    variables    system variables as "key => value" pairs
			 *
			 * Ports and sockets may be appended to the hostname.
			 *
			 * MySQLi driver config example:
			 *
			 */
            'hostname'   => 'vallery.catmgckfixum.sa-east-1.rds.amazonaws.com',
            'database'   => 'mak',
            'username'   => 'robsonrr',
            'password'   => 'Best94364811082',
            'persistent' => FALSE,
            'ssl'        => NULL,
        ],
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
    ],
    'mak' => [
        'type'       => 'MySQLi',
        'connection' => [
            'hostname'   => 'vallery.catmgckfixum.sa-east-1.rds.amazonaws.com',
            'database'   => 'mak',
            'username'   => 'robsonrr',
            'password'   => 'Best94364811082',
            'persistent' => FALSE,
            'ssl'        => NULL,
        ],
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
    ],  
    'webteam' => [
        'type'       => 'MySQLi',
        'connection' => [
            'hostname'   => 'webteam.catmgckfixum.sa-east-1.rds.amazonaws.com',
            'database'   => 'mak',
            'username'   => 'webteam',
            'password'   => 'hD8OBw5vLo1qyxt4',
            'persistent' => FALSE,
            'ssl'        => NULL,
        ],
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
    ],  
    'alternate' => [
        'type'       => 'PDO',
        'connection' => [
            /**
			 * The following options are available for PDO:
			 *
			 * string   dsn         Data Source Name
			 * string   username    database username
			 * string   password    database password
			 * boolean  persistent  use persistent connections?
			 */
            'dsn'        => 'mysql:host=localhost;dbname=kohana',
            'username'   => 'root',
            'password'   => 'r00tdb',
            'persistent' => FALSE,
        ],
        /**
		 * The following extra options are available for PDO:
		 *
		 * string   identifier  set the escaping identifier
		 */
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
    ],
];


<?php

return [
	'setting' => [
		'env' => ienv('SETTING_DEVELOPMENT', RELEASE),
		'error_reporting' =>ienv('SETTING_ERROR_REPORTING', E_ALL^E_NOTICE^E_WARNING^E_DEPRECATED^E_USER_DEPRECATED),
		'server' => ienv('SETTING_SERVERS', 'http|encrypt|queue'),
		'basedir' => [
			dirname(__DIR__, 3)
		]
	],
	'cache' => [
		'default' => [
			'driver' => ienv('CACHE_DEFAULT_DRIVER', 'redis'),
			'client' => ienv('CACHE_DEFAULT_REDIS_CLIENT', 'default')
		],
		'default1' => [
			'driver' => ienv('CACHE_DEFAULT_DRIVER', 'redis'),
			'client' => ienv('CACHE_DEFAULT_REDIS_CLIENT', 'default1')
		]
	],
	'redis' => [
		'default' => [
			'driver' => ienv('CACHE_DEFAULT_DRIVER', 'redis'),
			'host' => ienv('CACHE_DEFAULT_HOST', '127.0.0.1'),
			'port' => ienv('CACHE_DEFAULT_PORT', '6379'),
			'password' => ienv('CACHE_DEFAULT_PASSWORD', ''),
			'timeout' => ienv('CACHE_DEFAULT_TIMEOUT', '30'),
			'database' => ienv('CACHE_DEFAULT_DATABASE', '0'),
			'model' => ienv('CACHE_DEFAULT_MODEL_CACHE', false),
		],
		'default1' => [
			'driver' => ienv('CACHE_DEFAULT_DRIVER', 'redis'),
			'host' => ienv('CACHE_DEFAULT_HOST', '127.0.0.1'),
			'port' => ienv('CACHE_DEFAULT_PORT', '6379'),
			'password' => ienv('CACHE_DEFAULT_PASSWORD', ''),
			'timeout' => ienv('CACHE_DEFAULT_TIMEOUT', '30'),
			'database' => ienv('CACHE_DEFAULT_DATABASE1', '1'),
			'model' => ienv('CACHE_DEFAULT_MODEL_CACHE', false),
		]
	],
	'pool' => [
		'database' => [
			'default' => [
				'enable' => ienv('POOL_DATABASE_DEFAULT_ENABLE', false),
				'max' => ienv('POOL_DATABASE_DEFAULT_MAX', 20),
			],
			'addons' => [
				'enable' => ienv('POOL_DATABASE_ADDONS_ENABLE', false),
				'max' => ienv('POOL_DATABASE_ADDONS_MAX', 20),
			]
		],
		'redis' => [
			'default' => [
				'enable' => ienv('POOL_CACHE_DEFAULT_ENABLE', 1),
				'max' => ienv('POOL_CACHE_DEFAULT_MAX', 20),
			],
		]
	],
];
<?php

return [
	'api' => [
		'endpoint' => env('ACP_CLIENT_ENDPOINT', 'http://0.0.0.0/api/'),
		'credentials' => [
	        'username' => env('ACP_CLIENT_USERNAME'),
	        'password' => env('ACP_CLIENT_PASSWORD')
		]
	]
];

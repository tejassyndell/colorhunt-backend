<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_methods' => ['*'],

'allowed_origins' => ['http://localhost:4200'], // Add your frontend URL here

   
    'supportsCredentials' => false,
    'allowedOrigins' => ['*'],
    //'allowedOriginsPatterns' => [],
   // 'allowedHeaders' => ['*'],
	'allowedHeaders' => ['Content-Type', 'Origin', 'Accept', 'X-Requested-With', 'Authorization', 'Application', 'X-Auth-Token'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => [],
    'maxAge' => 0,
    'supports_credentials' => true,

];

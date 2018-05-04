 <?php

return [
	'server' => env('ARCGIS_SERVER'),
	'portal' => env('ARCGIS_PORTAL'),
	'token_url' => env('ARCGIS_SERVER') . '/tokens/generateToken', 
];

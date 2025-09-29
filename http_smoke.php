<?php
putenv('APP_ENV=local');
putenv('APP_DEBUG=true');
putenv('APP_KEY=base64:mdBIoSQoOPjw5+mGl5yF0el+N6Ee74HEjIFrpTEKG3s=');
require __DIR__.'/bootstrap/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/');
$response = $kernel->handle($request);
echo "Status: {$response->getStatusCode()}\n";
echo "Body:\n".$response->getContent()."\n";
$kernel->terminate($request, $response);

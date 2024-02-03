<?php

use okushnirov\core\Library\WebService;

require_once __DIR__.'/../../php/handler_error.php';

$webService = new WebService();
$ws = $webService->get('test', true);

echo "<pre>
<strong>WebService->get</strong> -> ";
print_r($ws);
echo "<pre>";

try {
  $response = $webService->request('test', '', $ws);
} catch (\Exception $e) {
  $response = 'Error -> '.$e->getMessage();
}

echo "<pre>
<strong>WebService->request</strong> -> $response
<pre>";

try {
  $response = $webService->xml('xml', '', post: 0);
} catch (\Exception $e) {
  $response = 'Error -> '.$e->getMessage();
}

echo "<pre>
<strong>WebService->xml</strong> -> ";
print_r($response);
echo "
<pre>";

try {
  $response = $webService->json('json', '', post: 0);
} catch (\Exception $e) {
  $response = 'Error -> '.$e->getMessage();
}

echo "<pre>
<strong>WebService->json</strong> -> ";
print_r($response);
echo "
<pre>";
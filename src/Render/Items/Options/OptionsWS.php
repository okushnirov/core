<?php

namespace okushnirov\core\Render\Items\Options;

use okushnirov\core\Library\{User, WebService};

class OptionsWS
{
  public static function get(?\SimpleXMLElement $xml):mixed
  {
    $wsName = trim($xml['name'] ?? '');
    $wsMethod = trim($xml['method'] ?? '');
    
    if ('' === $wsName || '' === $wsMethod) {
      
      return [];
    }
    
    $handlerClass = '';
    $handlerMethod = '';
    
    try {
      $webService = new WebService();
      
      $ws = $webService->get($wsName);
      
      $auth = trim($xml['auth'] ?? '');
      $handlerClass = trim($xml->handler['class'] ?? $handlerClass);
      $handlerMethod = trim($xml->handler['method'] ?? $handlerMethod);
      $request = trim($xml->request ?? '');
      
      if ('current:user' === $auth) {
        $ws = $webService->get($wsName);
        
        (new User());
        $ws->user = User::$login;
        $ws->pass = User::$pass;
      }
      
      if ('json' === $wsMethod) {
        $response = $webService->json($wsName, $request, $ws);
      } elseif ('xml' === $wsMethod) {
        $response = $webService->xml($wsName, $request, $ws);
      } else {
        $response = $webService->request($wsName, $request, $ws);
      }
    } catch (\Exception $e) {
      trigger_error(__METHOD__.' '.$e->getMessage());
      $response = [];
    }
    
    if (empty($response)) {
      
      return [];
    }
    
    if (class_exists($handlerClass) && method_exists($handlerClass, $handlerMethod)) {
      try {
        $result = (new $handlerClass)->$handlerMethod($response);
      } catch (\Exception) {
        try {
          $result = (new $handlerClass)::$handlerMethod($response);
        } catch (\Exception $e) {
          trigger_error(__METHOD__.' '.$e->getMessage());
        }
      }
    }
    
    return $result ?? [];
  }
}
<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\Charset;

final class WebService
{
  public static bool $debug = false;
  
  public static int $httpCode = 0;
  
  public static string $response = '';
  
  private static mixed $settings;
  
  public function __construct(mixed $settings = null)
  {
    self::$settings = $settings->ws ??
      File::parse(['/json/ws.json'])->ws ?? File::parse(['/json/settings.json'])->ws ?? [];
  }
  
  public function get(string $wsName, bool $test = TEST_SERVER):object
  {
    $data = self::$settings->{$wsName} ?? [];
    
    if (empty($data)) {
      if (self::$debug) {
        trigger_error(__METHOD__." Empty webservice settings", E_USER_ERROR);
      }
      
      throw new \Exception("Empty webservice settings", -1);
    }
    
    $ws = new \stdClass();
    $ws->url = $data->{'url'.($test ? 'Test' : '')} ?? $data->url ?? '';
    
    if ('' === $ws->url) {
      
      throw new \Exception("Empty webservice url", -2);
    }
    
    if (isset($data->connect)) {
      if ('string' === gettype(self::$settings->connection->{$data->connect})) {
        $ws->connection = self::$settings->connection->{$data->connect};
      } else {
        foreach (self::$settings->connection->{$data->connect} ?? [] as $key => $value) {
          $ws->{$key} = $value;
        }
      }
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__." [$wsName]\n".json_encode($ws, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        E_USER_ERROR);
    }
    
    return $ws;
  }
  
  public function json(
    string $wsName, string $data, ?object $ws = null, ?array $header = null, int $post = 1, int $ssl = 2,
    int    $timeout = 5, Charset $charset = Charset::UTF8):mixed
  {
    try {
      $response = $this->request($wsName, $data, $ws, $header, $post, $ssl, $timeout);
      $response = Charset::WINDOWS1251 === $charset ? Encoding::decode($response) : $response;
      $json = $response ? json_decode($response) : null;
      
      if (empty($json) || JSON_ERROR_NONE !== json_last_error()) {
        
        throw new \Exception(json_last_error_msg() ? : 'Empty or wrong response', -2);
      }
    } catch (\Exception $e) {
      
      throw new \Exception($e->getMessage(), -3);
    }
    
    return $json;
  }
  
  public function request(
    string $wsName, string $data = '', ?object $ws = null, ?array $header = null, int $post = 1, int $ssl = 2,
    int    $timeout = 5):string
  {
    try {
      $ws = empty($ws) ? $this->get($wsName) : $ws;
    } catch (\Exception) {
      
      throw new \Exception("Empty webservice settings", -1);
    }
    
    self::$httpCode = 0;
    self::$response = '';
    
    if (isset($ws->url)) {
      self::$response = Curl::exec($ws->url, $header ? : [], $data, $ws->user ?? false, $ws->pass ?? false, $post, $ssl,
        $timeout);
      self::$httpCode = Curl::$curlHttpCode;
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__." [$wsName]".($header ? " Header\n".json_encode($header,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '')."\nRequest $ws->url\n$data\nResponse [HTTP "
        .Curl::$curlHttpCode."]\n".self::$response, E_USER_ERROR);
    }
    
    if (200 !== Curl::$curlHttpCode) {
      
      throw new \Exception(self::$response, -2);
    }
    
    return self::$response;
  }
  
  public function xml(
    string $wsName, string $data, ?object $ws = null, ?array $header = null, int $post = 1, int $ssl = 2,
    int    $timeout = 5, Charset $charset = Charset::UTF8):\SimpleXMLElement
  {
    $data = Charset::WINDOWS1251 === $charset ? Encoding::encode($data) : $data;
    
    try {
      $response = $this->request($wsName, $data, $ws, $header, $post, $ssl, $timeout);
      $response = Charset::WINDOWS1251 === $charset ? Str::replaceHeader($response) : $response;
      $xml = $response && 200 === Curl::$curlHttpCode ? new \SimpleXMLElement($response) : null;
      
      if (empty($xml) || empty($xml->getName())) {
        
        throw new \Exception('Empty or wrong response', -2);
      }
    } catch (\Exception $e) {
      
      throw new \Exception($e->getMessage(), -3);
    }
    
    return $xml;
  }
}
<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\Charset;

final class WebService
{
  public static bool $debug = false;
  
  public static int $httpCode = 0;
  
  private static mixed $settings;
  
  public function __construct(mixed $settings = null)
  {
    self::$settings = $settings->ws ?? File::parse(['/json/settings.json'])->ws ?? [];
  }
  
  public function get(string $wsName, bool $test = TEST_SERVER):object
  {
    $data = self::$settings->{$wsName} ?? [];
    
    if (empty($data)) {
      if (self::$debug) {
        trigger_error(__METHOD__." Empty webservice settings");
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
      trigger_error(__METHOD__." [$wsName]\n".json_encode($ws, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
  
  /**
   * @deprecated
   */
  public function jsonWindows1251(
    string $wsName, string $data, ?object $ws = null, ?array $header = null, int $post = 1, int $ssl = 2,
    int    $timeout = 5):mixed
  {
    try {
      $response = Encoding::decode($this->request($wsName, $data, $ws, $header, $post, $ssl, $timeout));
      $json = $response ? json_decode($response) : null;
      
      if (empty($json) || JSON_ERROR_NONE !== json_last_error()) {
        
        throw new \Exception(json_last_error_msg() ? : 'Empty response', -2);
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
    
    if (isset($ws->url)) {
      $response = Curl::exec($ws->url, $header ? : [], $data, $ws->user ?? false, $ws->pass ?? false, $post, $ssl,
        $timeout);
      self::$httpCode = Curl::$curlHttpCode;
    } else {
      $response = '';
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__." [$wsName] Request $ws->url\n$data\nResponse [HTTP ".Curl::$curlHttpCode
        ."]\n$response");
    }
    
    if (200 !== Curl::$curlHttpCode) {
      trigger_error(__METHOD__." [$wsName] Request $ws->url\n$data\nResponse [HTTP ".Curl::$curlHttpCode
        ."]\n$response");
      
      throw new \Exception($response, -2);
    }
    
    return $response ? : '';
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
  
  /**
   * @deprecated
   */
  public function xmlUtf8(
    string $wsName, string $data, ?object $ws = null, ?array $header = null, int $post = 1, int $ssl = 2,
    int    $timeout = 5):\SimpleXMLElement
  {
    try {
      $response = $this->request($wsName, $data, $ws, $header, $post, $ssl, $timeout);
      $xml = $response && 200 === Curl::$curlHttpCode ? new \SimpleXMLElement($response) : null;
      
      if (empty($xml) || empty($xml->getName())) {
        
        throw new \Exception('Empty response', -2);
      }
    } catch (\Exception $e) {
      
      throw new \Exception($e->getMessage(), -3);
    }
    
    return $xml;
  }
  
  /**
   * @deprecated
   */
  public function xmlWindows1251(
    string $wsName, string $data, ?object $ws = null, ?array $header = null, int $post = 1, int $ssl = 2,
    int    $timeout = 5):\SimpleXMLElement
  {
    try {
      $response = $this->request($wsName, Encoding::encode($data), $ws, $header, $post, $ssl, $timeout);
      $response = Str::replaceHeader($response);
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
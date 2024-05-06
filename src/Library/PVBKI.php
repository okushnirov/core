<?php

namespace okushnirov\core\Library;

final class PVBKI
{
  public static string $code;
  
  public static bool $debug = false;
  
  public static bool $disabled = true;
  
  private static array $authKey = [];
  
  private static string $authName = '';
  
  private static string $pass = '';
  
  private static string $url = '';
  
  private static string $urlAuth = '';
  
  private static string $user = '';
  
  public function __construct()
  {
    $settings = File::parse(['/json/pvbki.json']);
    
    self::$disabled = $settings->disabled ?? self::$disabled;
    
    self::$authKey = $settings->key ?? self::$authKey;
    self::$authName = $settings->name ?? self::$authName;
    self::$pass = $settings->pass ?? '';
    self::$url = $settings->url ?? self::$url;
    self::$urlAuth = $settings->urlAuth ?? self::$urlAuth;
    self::$user = $settings->user ?? '';
  }
  
  public static function init(\stdClass $data):void
  {
    if (self::$debug) {
      trigger_error(__METHOD__."\n".json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    self::$code = $data->code ?? '';
  }
  
  public static function sendRequest():string
  {
    try {
      $client = new \SoapClient(self::$url, [
        'trace' => 1,
        'exceptions' => 1
      ]);
    } catch (\SoapFault $e) {
      $client = '';
      if (self::$debug) {
        trigger_error(__METHOD__.' Soap error: '.$e->getMessage());
      }
    }
    
    if (empty($client)) {
      
      return '';
    }
    
    try {
      $client->__setSoapHeaders([
        new \SoapHeader(self::$urlAuth, 'AuthenticationCredential', [
          'UserName' => self::$user,
          'Password' => self::$pass
        ], 'false'),
        new \SoapHeader(self::$urlAuth, 'AuthenticationIdentity', [
          'Name' => self::$authName,
          'Key' => implode(array_map('chr', self::$authKey))
        ], 'false')
      ]);
    } catch (\Exception $e) {
      if (self::$debug) {
        trigger_error(__METHOD__.' Soap auth error: '.$e->getMessage());
      }
    }
    
    try {
      $result = $client->Statement(['forID' => self::$code]);
    } catch (\Exception $e) {
      $result = '';
    }
    
    if (is_soap_fault($result)) {
      $response = $result->faultstring ?? '';
    } else {
      $response = $result->{'Report-StatementResult'} ?? '';
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__." Response\n".$response);
    }
    
    return $response;
  }
}
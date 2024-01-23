<?php

namespace okushnirov\core\Library;

final class PVBKI
{
  public static string $code;
  
  public static bool $debug = false;
  
  public static bool $disabled = true;
  
  private static array $_authKey = [];
  
  private static string $_authName = '';
  
  private static string $_pass = '';
  
  private static string $_url = '';
  
  private static string $_urlAuth = '';
  
  private static string $_user = '';
  
  public function __construct()
  {
    $settings = File::parse(['/json/pvbki.json']);
    
    self::$disabled = $settings->disabled ?? self::$disabled;
    
    self::$_authKey = $settings->key ?? self::$_authKey;
    self::$_authName = $settings->name ?? self::$_authName;
    self::$_pass = $settings->pass ?? '';
    self::$_url = $settings->url ?? self::$_url;
    self::$_urlAuth = $settings->urlAuth ?? self::$_urlAuth;
    self::$_user = $settings->user ?? '';
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
      $client = new \SoapClient(self::$_url, [
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
        new \SoapHeader(self::$_urlAuth, 'AuthenticationCredential', [
          'UserName' => self::$_user,
          'Password' => self::$_pass
        ], 'false'),
        new \SoapHeader(self::$_urlAuth, 'AuthenticationIdentity', [
          'Name' => self::$_authName,
          'Key' => implode(array_map('chr', self::$_authKey))
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
<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\HTTPMethods;

final class Curl
{
  public static int | bool $curlHttpCode = false;
  
  public static mixed $curlHttpInfo;
  
  public static function exec(
    string       $url, array $header = [], mixed $data = '', string $userLogin = '', string $userPassword = '',
    ?HTTPMethods $httpMethod = HTTPMethods::POST, bool | int $ssl = false, int $timeout = 10):bool | string
  {
    if (empty($url)) {
      $back = debug_backtrace();
      trigger_error(__METHOD__." url=[$url]\n".json_encode($back, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, is_null($httpMethod) ? HTTPMethods::POST->value : $httpMethod->value);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data ? : '');
    
    if ($_SERVER['HTTP_USER_AGENT'] ?? '') {
      curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    }
    
    if (!empty($header)) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    
    if (!empty($userLogin)) {
      curl_setopt($ch, CURLOPT_USERPWD, "$userLogin:$userPassword");
    }
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl ? 2 : 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !!$ssl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, !!$ssl);
    
    $response = curl_exec($ch);
    
    self::$curlHttpInfo = curl_getinfo($ch);
    self::$curlHttpCode = (int)(self::$curlHttpInfo['http_code'] ?? 0);
    
    curl_close($ch);
    
    return $response;
  }
}
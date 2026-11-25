<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\HTTPMethods;

final class Curl
{
  public static int | bool $curlHttpCode = false;
  public static mixed $curlHttpInfo;
  public static array $ignoreLogStatusCodes = [];
  
  public static function exec(
    string       $url, array $header = [], mixed $data = '', string $userLogin = '', string $userPassword = '',
    ?HTTPMethods $httpMethod = HTTPMethods::POST, bool | int $ssl = false, int $timeout = 10, bool $isDebug = false,
    array        $options = []):bool | string
  {
    $back = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    
    if ('' === $url) {
      trigger_error(__METHOD__." URL is empty!\n".json_encode($back,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
      
      return false;
    }
    
    $ignoreStatusCodes = [];
    
    if (!empty($options)) {
      if (array_is_list($options)) {
        $ignoreStatusCodes = $options;
      } else {
        $ignoreStatusCodes = $options['ignore_status_codes'] ?? [];
      }
    }
    
    $method = $httpMethod?->value ?? HTTPMethods::POST->value;
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if (!empty($data)) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
      curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    }
    
    if (!empty($header)) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    
    if ('' !== $userLogin) {
      curl_setopt($ch, CURLOPT_USERPWD, "$userLogin:$userPassword");
    }
    
    $verifySSL = (bool)$ssl;
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySSL ? 2 : 0);
    
    if (defined('CURLOPT_SSL_VERIFYSTATUS')) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, $verifySSL);
    }
    
    $response = curl_exec($ch);
    
    $curlError = curl_error($ch);
    
    self::$curlHttpInfo = curl_getinfo($ch);
    self::$curlHttpCode = (int)(self::$curlHttpInfo['http_code'] ?? 0);
    
    unset($ch);
    
    $isSuccessHttpCode = 200 <= self::$curlHttpCode && 300 > self::$curlHttpCode;
    $isFailed = $response === false || !$isSuccessHttpCode;
    
    $ignoredCodes = array_merge(self::$ignoreLogStatusCodes, $ignoreStatusCodes);
    $isIgnoredCode = in_array(self::$curlHttpCode, $ignoredCodes, true);
    
    if (($isDebug || $isFailed) && !$isIgnoredCode) {
      $logPrefix = $isFailed ? "[ERROR RESPONSE]" : "[DEBUG RESPONSE]";
      
      $responseLog = [
        'URL' => $url,
        'METHOD' => $httpMethod?->value ?? HTTPMethods::POST->value,
        'HTTP_CODE' => self::$curlHttpCode,
        'CURL_ERROR' => $curlError ? : 'None',
        'RESPONSE' => $response === false ? 'FALSE (Request Failed)' : $response,
        'HEADERS' => $header,
        'DATA' => $data,
        'SSL_VERIFY' => (bool)$ssl,
        'TIMEOUT' => $timeout,
        'OPTIONS' => $options,
        'STACK_TRACE' => $back
      ];
      
      trigger_error(__METHOD__." $logPrefix:\n".json_encode($responseLog,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
    }
    
    return $response;
  }
}
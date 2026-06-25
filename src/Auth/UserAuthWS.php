<?php

namespace okushnirov\core\Auth;

use okushnirov\core\Library\{Config, Curl, Enums\CookieType, Enums\SessionType, Lang};

final class UserAuthWS
{
  private ?int $userID;
  private string $userLogin;
  
  public function __construct(
    string $userLogin = '', ?int $userID = null, SessionType $sessionType = SessionType::WS)
  {
    $this->userID = $userID;
    $this->userLogin = $userLogin;
    
    Lang::set($sessionType, CookieType::No);
  }
  
  public function get():\SimpleXMLElement
  {
    Config::load(['login.php']);
    
    $settings = Config::getAsObject();
    
    if (empty($settings) || !isset($settings->login->auth)) {
      
      throw new \Exception('Empty login auth settings', -10);
    }
    
    $ws = $settings->login->auth;
    
    $queryParam = !is_null($this->userID) && 0 < $this->userID ? ['userID' => $this->userID]
      : ['userLogin' => $this->userLogin];
    
    $urlKey = 'url'.(TEST_SERVER ? 'Test' : '');
    $urlApi = $ws->{$urlKey} ?? $ws->url ?? '';
    
    $response = Curl::exec($urlApi, [
      "charset=\"utf-8\"",
      "Authorization: Basic ".base64_encode("$ws->user:$ws->pass")
    ], http_build_query($queryParam), ssl: 2, timeout: 5);
    
    try {
      $xmlResponse = empty($response) ? null : new \SimpleXMLElement($response);
    } catch (\Exception) {
      $xmlResponse = null;
    }
    
    if (empty($xmlResponse) || !isset($xmlResponse->result->error)) {
      $errorCode = 200 === Curl::$curlHttpCode ? -20 : -30;
      
      throw new \Exception($settings->login->error->{$errorCode}->{Lang::$lang} ??
        $settings->login->error->{$errorCode} ?? 'Empty user info', $errorCode);
    }
    
    if ((int)$xmlResponse->result->error) {
      $errorCode = -1 === (int)$xmlResponse->result->error ? -40 : -50;
      $errorText = $settings->login->error->{$errorCode}->{Lang::$lang} ?? 'WS Error';
      
      throw new \Exception(trim($errorText.' #'.$xmlResponse->result->error), $errorCode);
    }
    
    $nodes = $xmlResponse->xpath('user');
    $user = $nodes[0] ?? null;
    
    if (is_null($user)) {
      
      throw new \Exception($settings->login->error->{-60}->{Lang::$lang} ??
        $settings->login->error->{-60} ?? 'Empty user info', -60);
    }
    
    return $user;
  }
}
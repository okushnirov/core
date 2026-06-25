<?php

namespace okushnirov\core\Auth;

use okushnirov\core\Auth\Interfaces\AuthStrategy;
use okushnirov\core\Library\{Config, Lang, WebService};

class AuthStrategyWs implements AuthStrategy
{
  private bool $isAppendData;
  private bool $isDebug;
  private UserSession $session;
  
  public function __construct(UserSession $userSession, bool $isAppendData, bool $isDebug)
  {
    $this->session = $userSession;
    $this->isAppendData = $isAppendData;
    $this->isDebug = $isDebug;
  }
  
  public function authenticate():bool
  {
    Config::load(['login.php'], Lang::$language);
    
    $settings = Config::getAsObject();
    
    if (empty($settings) || !isset($settings->login->ws->auth)) {
      
      throw new \Exception('Empty ws auth settings', -10);
    }
    
    $webService = new WebService($settings->login);
    $webService::$isDebug = $this->isDebug;
    
    $request = [
      'request' => [
        'type' => 'get',
        'lang' => Lang::getShort(Lang::$lang)
      ]
    ];
    
    if ($this->isAppendData) {
      $request['password'] = $this->session->getUserPassword();
    }
    
    try {
      $jsonUserData = $webService->json('auth', json_encode($request), header: [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic '.base64_encode($this->session->getUserLogin().':'.$this->session->getUserPassword())
      ], timeout: 3);
    } catch (\Exception $e) {
      $jsonUserData = new \stdClass();
      
      if ($this->isDebug) {
        trigger_error(__METHOD__." WebService Exception: ".$e->getMessage());
      }
    }
    
    if (401 === $webService::$httpCode) {
      
      throw new \Exception($settings->login->error->{-115}->{Lang::$lang} ??
        $settings->login->error->{-115} ?? 'Unauthorized', -115);
    }
    
    if (200 !== $webService::$httpCode || empty((array)$jsonUserData)) {
      
      throw new \Exception($settings->login->error->{-20}->{Lang::$lang} ??
        $settings->login->error->{-20} ?? 'Bad Gateway', -20);
    }
    
    if (!isset($jsonUserData->serviceInfo) || 0 !== $jsonUserData->serviceInfo->errorCode) {
      $errorCode = $jsonUserData->serviceInfo->errorCode ?? -50;
      
      throw new \Exception("Service error code: $errorCode", -50);
    }
    
    if (!isset($jsonUserData->user)) {
      
      throw new \Exception($settings->login->error->{-60}->{Lang::$lang} ??
        $settings->login->error->{-60} ?? 'User data missing', -60);
    }
    
    $this->session->authorizeUser($jsonUserData->user);
    
    if ($this->isAppendData) {
      $this->session->setCRC('ws:data', $jsonUserData->data ?? '');
    }
    
    return true;
  }
}
<?php

namespace okushnirov\core\Library;

final class PVBKI
{
  public bool $isDisabled = true;
  
  private array $authKey = [];
  private string $authName = '';
  private string $code;
  private bool $isDebug = false;
  private bool $isLoaded = false;
  private string $pass = '';
  private string $url = '';
  private string $urlAuth = '';
  private string $user = '';
  
  public function init(object $request, bool $isDebug = false):void
  {
    self::loadSettings();
    
    $this->isDebug = $isDebug || $this->isDebug;
    
    if ($this->isDebug) {
      trigger_error(__METHOD__."\n".json_encode($request,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), E_USER_ERROR);
    }
    
    $this->code = trim((string)($request->code ?? ''));
  }
  
  public function sendRequest():string
  {
    if (!$this->isLoaded) {
      
      throw new \Exception('Initialization not completed or service settings missing', -20);
    }
    
    if ($this->isDisabled) {
      
      return '';
    }
    
    if (empty($this->url)) {
      
      throw new \Exception('Wrong URL service', -30);
    }
    
    if ('' === $this->code) {
      
      throw new \Exception('Empty client code', -40);
    }
    
    try {
      $client = new \SoapClient($this->url, [
        'trace' => 1,
        'exceptions' => 1
      ]);
      
      $binaryKey = empty($this->authKey) ? '' : implode(array_map('chr', $this->authKey));
      
      $client->__setSoapHeaders([
        new \SoapHeader($this->urlAuth, 'AuthenticationCredential', [
          'UserName' => $this->user,
          'Password' => $this->pass
        ], 'false'),
        new \SoapHeader($this->urlAuth, 'AuthenticationIdentity', [
          'Name' => $this->authName,
          'Key' => $binaryKey
        ], 'false')
      ]);
      
      $result = $client->Statement(['forID' => $this->code]);
      
      if (is_soap_fault($result)) {
        $response = $result->faultstring ?? '';
      } else {
        $response = $result->{'Report-StatementResult'} ?? '';
      }
      
      if ($this->isDebug) {
        trigger_error(__METHOD__." Soap response\n".$response);
      }
      
      return $response;
    } catch (\SoapFault $e) {
      if ($this->isDebug) {
        trigger_error(__METHOD__.' Soap error: '.$e->getMessage(), E_USER_ERROR);
      }
      
      return '';
    } catch (\Exception $e) {
      if ($this->isDebug) {
        trigger_error(__METHOD__.' General error: '.$e->getMessage(), E_USER_ERROR);
      }
      
      return '';
    }
  }
  
  private function loadSettings():void
  {
    Config::load(['pvbki.php']);
    
    if (!Config::isLoaded()) {
      
      throw new \Exception('Error reading configuration file', -10);
    }
    
    $this->authKey = Config::get('key') ?? $this->authKey;
    $this->authName = Config::get('name') ?? $this->authName;
    $this->isDebug = Config::get('debug') ?? $this->isDebug;
    $this->isDisabled = Config::get('disabled') ?? $this->isDisabled;
    $this->isLoaded = true;
    $this->pass = Config::get('pass') ?? '';
    $this->url = Config::get('url') ?? $this->url;
    $this->urlAuth = Config::get('urlAuth') ?? $this->urlAuth;
    $this->user = Config::get('user') ?? '';
  }
}
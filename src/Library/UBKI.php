<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\{FileType, HeaderXML, UBKITypes};

final class UBKI
{
  public bool $isDisabled = true;
  
  private string $birthday = '';
  private string $code = '';
  private string $docNumber = '';
  private string $docSerial = '';
  private int $docType;
  private string $firstName = '';
  private bool $isDebug = false;
  private bool $isLoaded = false;
  private bool $isTestMode = false;
  private string $lastName = '';
  private string $middleName = '';
  private string $pass = '';
  private string $phone = '';
  private ?UBKITypes $requestType;
  private string $sessionID = '';
  private string $sessionIDFile = '';
  private string $url = '';
  private string $urlAuth = '';
  private string $urlTest = '';
  private string $user = '';
  
  public function getSessionID():bool
  {
    if ($this->isDisabled) {
      
      return false;
    }
    
    $fileData = File::parse([$this->sessionIDFile], FileType::SERIALIZE);
    $this->sessionID = $fileData->{date('Y-m-d')} ?? '';
    
    if ($this->isDebug) {
      trigger_error(__METHOD__.' '.json_encode($fileData, JSON_PRETTY_PRINT)."\nSessionID = ".$this->sessionID);
    }
    
    # Get new sessionID
    if (empty($this->sessionID)) {
      $header = [
        "Content-Type: application/json",
        "Accept: application/json"
      ];
      
      $request = json_encode([
        "doc" => [
          "auth" => [
            "login" => $this->user,
            "pass" => $this->pass
          ]
        ]
      ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      
      $response = json_decode(Curl::exec($this->urlAuth, $header, $request));
      
      if ($this->isDebug) {
        trigger_error(__METHOD__." URL ".$this->urlAuth."\nHeader:\n".json_encode($header,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\nRequest:\n$request\nResponse:\n"
          .json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
      }
      
      $this->sessionID = $response->doc->auth->sessid ?? '';
      
      if ($this->sessionID) {
        
        return FileIO::writeFile($_SERVER['DOCUMENT_ROOT'].$this->sessionIDFile,
          serialize([date('Y-m-d') => $this->sessionID]));
      }
    }
    
    return !empty($this->sessionID);
  }
  
  public function init(?UBKITypes $type, \stdClass $request, bool $isDebug = false, bool $isTestMode = false):void
  {
    self::loadSettings();
    
    $this->isDebug = $isDebug || $this->isDebug;
    $this->isTestMode = $isTestMode;
    
    if ($this->isDebug) {
      trigger_error(__METHOD__." Request type = $type?->name\n".json_encode($request,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    
    $this->requestType = $type;
    
    $this->birthday = trim((string)($request->birthday ?? ''));
    $this->code = trim((string)($request->code ?? ''));
    $this->docNumber = trim((string)($request->docNumber ?? ''));
    $this->docSerial = trim((string)($request->docSerial ?? ''));
    $this->docType = max((int)($request->docType ?? 1), 1);
    $this->firstName = trim((string)($request->firstName ?? ''));
    $this->lastName = trim((string)($request->lastName ?? ''));
    $this->middleName = trim((string)($request->middleName ?? ''));
    $this->phone = substr($request->phone ?? '', -10);
    $this->phone = preg_match('/^0\d{9}$/', $this->phone) ? '+38'.$this->phone : '';
  }
  
  public function sendRequest(string $request = ''):string
  {
    if (!$this->isLoaded) {
      
      throw new \Exception('Initialization not completed or service settings missing', -20);
    }
    
    if ($this->isDisabled) {
      
      return '';
    }
    
    $request = '' === $request ? self::getRequest() : $request;
    $header = [
      "Content-type: application/xml;charset=utf-8",
      "Accept: application/xml",
      "Content-length: ".strlen($request)
    ];
    $url = trim($this->isTestMode ? $this->urlTest : $this->url);
    
    if ('' === $request) {
      
      throw new \Exception('Empty request', -40);
    }
    
    if (empty($url)) {
      
      throw new \Exception('Wrong URL service', -30);
    }
    
    if ($this->isDebug) {
      trigger_error(__METHOD__." URL $url\nHeader:\n".json_encode($header,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\nRequest:\n".$request);
    }
    
    $response = $request ? (string)Curl::exec($url, $header, $request) : '';
    
    if ($this->isDebug) {
      trigger_error(__METHOD__." Response HTTP ".Curl::$curlHttpCode."\n$response".(200 === Curl::$curlHttpCode
          ? ''
          : "\nCurl Info\n".json_encode(Curl::$curlHttpInfo,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    }
    
    return 200 === Curl::$curlHttpCode ? $response : '';
  }
  
  private function getRequest():string
  {
    $xmlRequest = match ($this->requestType) {
      UBKITypes::Request10 => '<request reqtype="'.UBKITypes::Request10->value.'" reqreason="1" reqdate="'.date("Y-m-d")
        .'" reqsource="1">
  <i reqlng="1">
    <ident okpo="'.htmlspecialchars($this->code, ENT_XML1).'" lname="'.htmlspecialchars($this->lastName, ENT_XML1)
        .'" fname="'.htmlspecialchars($this->firstName, ENT_XML1).'" mname="'.htmlspecialchars($this->middleName,
          ENT_XML1).'" bdate="'.htmlspecialchars($this->birthday, ENT_XML1).'"/>'.(empty($this->phone)
          ? ''
          : '
    <contacts>
      <cont ctype="3" cval="'.htmlspecialchars($this->phone, ENT_XML1).'"/>
    </contacts>').('' === $this->docNumber || '' === $this->docSerial
          ? ''
          : '
    <docs>
      <doc dtype="'.$this->docType.'" dser="'.htmlspecialchars($this->docSerial, ENT_XML1).'" dnom="'
          .htmlspecialchars($this->docNumber, ENT_XML1).'"/>
    </docs>
    <mvd dtype="'.$this->docType.'" pser="'.htmlspecialchars($this->docSerial, ENT_XML1).'" pnom="'.$this->docNumber
          .'" plname="'.$this->lastName.'" pfname="'.htmlspecialchars($this->firstName, ENT_XML1).'" pmname="'
          .htmlspecialchars($this->middleName, ENT_XML1).'" pbdate="'.htmlspecialchars($this->birthday, ENT_XML1).'"/>')
        .'
  </i>
</request>',
      UBKITypes::Request15, UBKITypes::Request26 => '<request reqtype="'.$this->requestType->value
        .'" reqreason="12" reqdate="'.date("Y-m-d").'" reqsource="1"><i reqlng="1"><ident okpo="'
        .htmlspecialchars($this->code, ENT_XML1).'"/></i></request>',
      UBKITypes::Request22 => '<request reqtype="'.$this->requestType->value.'" reqreason="6" reqdate="'.date("Y-m-d")
        .'" reqsource="1"><i reqlng="1"><ident okpo="'.htmlspecialchars($this->code, ENT_XML1).'"/></i></request>',
      default => '',
    };
    
    if ($this->isDebug) {
      trigger_error(__METHOD__.' Request by type '.$xmlRequest);
    }
    
    if (!$this->sessionID && !self::getSessionID()) {
      
      return '';
    }
    
    return $xmlRequest ? HeaderXML::UTF->value.'<doc><ubki sessid="'.$this->sessionID
      .'"><req_envelope descr="Моніторинг контрагента"><req_xml>'.base64_encode($xmlRequest)
      .'</req_xml></req_envelope></ubki></doc>' : '';
  }
  
  private function loadSettings():void
  {
    Config::load(['ubki.php']);
    
    if (!Config::isLoaded()) {
      
      throw new \Exception('Error reading configuration file', -10);
    }
    
    $this->isDebug = Config::get('debug') ?? $this->isDebug;
    $this->isDisabled = Config::get('disabled') ?? $this->isDisabled;
    $this->isLoaded = true;
    $this->pass = Config::get('pass') ?? '';
    $this->sessionIDFile = Config::get('sessionFile') ?? $this->sessionIDFile;
    $this->url = Config::get('url') ?? $this->url;
    $this->urlAuth = Config::get('urlAuth') ?? $this->urlAuth;
    $this->urlTest = Config::get('urlTest') ?? $this->urlTest;
    $this->user = Config::get('user') ?? '';
  }
}
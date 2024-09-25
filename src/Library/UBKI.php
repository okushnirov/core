<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\{FileType, HeaderXML, UBKITypes};

final class UBKI
{
  public static string $birthday;
  
  public static string $code;
  
  public static bool $debug = false;
  
  public static bool $disabled = true;
  
  public static string $docNumber;
  
  public static string $docSerial;
  
  public static int $docType;
  
  public static string $firstName;
  
  public static string $lastName;
  
  public static string $middleName;
  
  public static string $phone;
  
  public static ?UBKITypes $requestType;
  
  public static bool $testMode = false;
  
  private static string $pass = '';
  
  private static string $sessionID = '';
  
  private static string $sessionIDFile = '';
  
  private static string $url = '';
  
  private static string $urlAuth = '';
  
  private static string $urlTest = '';
  
  private static string $user = '';
  
  public function __construct(bool $testMode = false, bool $debug = false)
  {
    $settings = File::parse(['/json/ubki.json']);
    
    self::$debug = $debug || ($settings->debug ?? self::$debug);
    self::$disabled = $settings->disabled ?? self::$disabled;
    self::$testMode = $testMode;
    
    self::$pass = $settings->pass ?? '';
    self::$sessionIDFile = $settings->sessionFile ?? self::$sessionIDFile;
    self::$url = $settings->url ?? self::$url;
    self::$urlAuth = $settings->urlAuth ?? self::$urlAuth;
    self::$urlTest = $settings->urlTest ?? self::$urlTest;
    self::$user = $settings->user ?? '';
    
    if (self::$debug) {
      trigger_error(__METHOD__." Settings\n".json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
  }
  
  public static function getSessionID():bool
  {
    if (self::$disabled) {
      
      return false;
    }
    
    $fileData = File::parse([self::$sessionIDFile], FileType::SERIALIZE);
    self::$sessionID = $fileData->{date('Y-m-d')} ?? '';
    
    if (self::$debug) {
      trigger_error(__METHOD__.' '.json_encode($fileData, JSON_PRETTY_PRINT)."\nSessionID = ".self::$sessionID);
    }
    
    # Get new sessionID
    if (empty(self::$sessionID)) {
      $response = json_decode(Curl::exec(self::$urlAuth, [
        "Content-Type: application/json",
        "Accept: application/json"
      ], json_encode([
        "doc" => [
          "auth" => [
            "login" => self::$user,
            "pass" => self::$pass
          ]
        ]
      ])));
      
      if (self::$debug) {
        trigger_error(__METHOD__." Authorization response:\n".json_encode($response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
      }
      
      self::$sessionID = $response->doc->auth->sessid ?? '';
      
      if (self::$sessionID) {
        
        return FileIO::writeFile($_SERVER['DOCUMENT_ROOT'].self::$sessionIDFile,
          serialize([date('Y-m-d') => self::$sessionID]));
      }
    }
    
    return !empty(self::$sessionID);
  }
  
  public static function init(?UBKITypes $type, \stdClass $data):void
  {
    if (self::$debug) {
      trigger_error(__METHOD__." Request type = $type?->name\n".json_encode($data,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    self::$requestType = $type;
    
    self::$birthday = $data->birthday ?? '';
    self::$code = $data->code ?? '';
    self::$docNumber = $data->docNumber ?? '';
    self::$docSerial = $data->docSerial ?? '';
    self::$docType = max((int)($data->docType ?? 1), 1);
    self::$firstName = $data->firstName ?? '';
    self::$lastName = $data->lastName ?? '';
    self::$middleName = $data->middleName ?? '';
    self::$phone = substr($data->phone ?? '', -10);
    self::$phone = preg_match('/^0\d{9}$/', self::$phone ?? '') ? '+38'.self::$phone : '';
  }
  
  public static function sendRequest(string $request = ''):string
  {
    if (self::$disabled) {
      trigger_error(__METHOD__." Сервіс вимкнуто");
      
      return '';
    }
    
    $request = '' === $request ? self::_getRequest() : $request;
    
    if (self::$debug) {
      trigger_error(__METHOD__." Request\n".$request);
    }
    
    $response = $request ? (string)Curl::exec(self::$testMode ? self::$urlTest : self::$url, [
      "POST ".(self::$testMode ? self::$urlTest : self::$url)." HTTP/1.0",
      "Content-type: text/xml;charset=\"utf-8\"",
      "Accept: text/xml",
      "Content-length: ".strlen($request)
    ], $request) : '';
    
    if (self::$debug) {
      trigger_error(__METHOD__."HTTP ".Curl::$curlHttpCode." Response\n".$response);
    }
    
    return 200 === Curl::$curlHttpCode ? $response : '';
  }
  
  private static function _getRequest():string
  {
    $xmlRequest = match (self::$requestType) {
      UBKITypes::Request10 => '<request reqtype="'.UBKITypes::Request10->value.'" reqreason="1" reqdate="'.date("Y-m-d")
        .'" reqsource="1">
  <i reqlng="1">
    <ident okpo="'.htmlspecialchars(self::$code, ENT_XML1).'" lname="'.htmlspecialchars(self::$lastName, ENT_XML1)
        .'" fname="'.htmlspecialchars(self::$firstName, ENT_XML1).'" mname="'.htmlspecialchars(self::$middleName,
          ENT_XML1).'" bdate="'.htmlspecialchars(self::$birthday, ENT_XML1).'"/>'.(empty(self::$phone)
          ? ''
          : '
    <contacts>
      <cont ctype="3" cval="'.htmlspecialchars(self::$phone, ENT_XML1).'"/>
    </contacts>').('' === self::$docNumber || '' === self::$docSerial
          ? ''
          : '
    <docs>
      <doc dtype="'.self::$docType.'" dser="'.htmlspecialchars(self::$docSerial, ENT_XML1).'" dnom="'
          .htmlspecialchars(self::$docNumber, ENT_XML1).'"/>
    </docs>
    <mvd dtype="'.self::$docType.'" pser="'.htmlspecialchars(self::$docSerial, ENT_XML1).'" pnom="'.self::$docNumber
          .'" plname="'.self::$lastName.'" pfname="'.htmlspecialchars(self::$firstName, ENT_XML1).'" pmname="'
          .htmlspecialchars(self::$middleName, ENT_XML1).'" pbdate="'.htmlspecialchars(self::$birthday, ENT_XML1).'"/>')
        .'
  </i>
</request>',
      UBKITypes::Request15, UBKITypes::Request26 => '<request reqtype="'.self::$requestType->value
        .'" reqreason="2" reqdate="'.date("Y-m-d").'" reqsource="1"><i reqlng="1"><ident okpo="'
        .htmlspecialchars(self::$code, ENT_XML1).'"/></i></request>',
      UBKITypes::Request22 => '<request reqtype="'.self::$requestType->value.'" reqreason="6" reqdate="'.date("Y-m-d")
        .'" reqsource="1"><i reqlng="1"><ident okpo="'.htmlspecialchars(self::$code, ENT_XML1).'"/></i></request>',
      default => '',
    };
    
    if (self::$debug) {
      trigger_error(__METHOD__.' Request by type '.$xmlRequest);
    }
    
    if (!self::$sessionID && !self::getSessionID()) {
      
      return '';
    }
    
    return $xmlRequest ? HeaderXML::UTF->value.'<doc><ubki sessid="'.self::$sessionID
      .'"><req_envelope descr="Моніторинг контрагента"><req_xml>'.base64_encode($xmlRequest)
      .'</req_xml></req_envelope></ubki></doc>' : '';
  }
}
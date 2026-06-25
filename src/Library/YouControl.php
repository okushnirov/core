<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\{CustomerType, HTTPMethods, YouControlTypes};

final class YouControl
{
  public bool $isDisabled = true;
  
  private string $APIKeyAnalytics = '';
  private string $APIKeyData = '';
  private string $birthday = '';
  private string $code = '';
  private string $documentNumber = '';
  private string $documentSeries = '';
  private string $firstName = '';
  private bool $isDebug = false;
  private bool $isLoaded = false;
  private string $lastName = '';
  private string $middleName = '';
  private object $prevResult;
  private ?CustomerType $subject = null;
  private string $url = '';
  
  public function __construct()
  {
    $this->prevResult = new \stdClass();
  }
  
  public function init(array $request, bool $isDebug = false):void
  {
    $this->loadSettings();
    
    $this->isDebug = $isDebug || $this->isDebug;
    
    if ($this->isDebug) {
      trigger_error(__METHOD__." Request\n".json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    # Тип контрагента
    $this->subject = CustomerType::getType(trim($request['subject'] ?? ''));
    
    # Код (ЄДРПОУ/РНОКПП)
    $this->code = $request['code'] ?? $this->code;
    
    # Дата народження
    $this->birthday = $request['birthday'] ?? $this->birthday;
    
    # ПІБ контрагента
    $this->lastName = $request['lastName'] ?? $this->lastName;
    $this->firstName = $request['firstName'] ?? $this->firstName;
    $this->middleName = $request['middleName'] ?? $this->middleName;
    
    # Документ
    $this->documentNumber = $request['documentNumber'] ?? $this->documentNumber;
    $this->documentSeries = $request['documentSeries'] ?? $this->documentSeries;
    
    # Попередній результат перевірки (за наявності)
    $this->prevResult = $request['result'] ?? $this->prevResult;
  }
  
  public function sendRequest():string
  {
    if (!$this->isLoaded) {
      
      throw new \Exception('Initialization not completed or service settings missing', -20);
    }
    
    if ($this->isDisabled) {
      
      return '';
    }
    
    return (string)json_encode(match ($this->subject) {
      CustomerType::PERSON => $this->runPersonal(),
      CustomerType::BUSINESSMAN => $this->runBusinessman(),
      CustomerType::COMPANY => $this->runCompany(),
      default => ''
    }, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
  }
  
  /**
   * Отримати фотографію
   *
   * @param object $photo
   *
   * @return void
   */
  private function getPhoto(object $photo):void
  {
    $url = trim($photo->url ?? '');
    
    if ('' === $url) {
      
      return;
    }
    
    # Отримуємо зміст фотографії за посиланням
    $file = file_get_contents("$url?apiKey=$this->APIKeyData");
    
    if (empty($file)) {
      
      return;
    }
    
    $contentType = '';
    $headers = $http_response_header ?? [];
    
    # Визначення типу зображення
    foreach ($headers as $header) {
      if (str_contains(strtoupper($header), "CONTENT-TYPE")) {
        $contentType = trim(str_ireplace([
          'Content-Type',
          ':'
        ], '', $header));
      }
    }
    
    # Тип не визначено
    if ('' === $contentType) {
      
      return;
    }
    
    # Збереження фотографії
    $photo->photo = "data:$contentType;base64, ".base64_encode($file);
  }
  
  /**
   * Результат попереднього запиту
   *
   * @param string $nameCheck
   *
   * @return mixed
   */
  private function getPreviousResult(string $nameCheck):mixed
  {
    if ($this->isDebug) {
      trigger_error(__METHOD__." Check [$nameCheck] isset[".isset($this->prevResult->{$nameCheck}).'] type ['
        .gettype($this->prevResult->{"$nameCheck"} ?? null).']');
    }
    
    return isset($this->prevResult->{$nameCheck}) && 'string' !== gettype($this->prevResult->{$nameCheck})
      ? $this->prevResult->{$nameCheck} : null;
  }
  
  /**
   * Формування строки запиту
   *
   * @return string
   */
  private function getQuery():string
  {
    $query = "LastName=".urlencode($this->lastName);
    $query .= "&FirstName=".urlencode($this->firstName);
    $query .= '' === $this->middleName ? '' : "&MiddleName=".urlencode($this->middleName);
    
    return $query;
  }
  
  /**
   * Формування URL запиту
   *
   * @param string $url
   * @param string $apiKey
   *
   * @return string
   */
  private function getURL(string $url, string $apiKey):string
  {
    $_url = str_starts_with(strtolower($url), 'http') ? '' : $this->url;
    $_url .= $url;
    $_url .= false === mb_stripos($url, '?') ? '?' : '&';
    $_url .= "apiKey=$apiKey";
    
    return $_url;
  }
  
  private function loadSettings():void
  {
    Config::load(['you-control.php']);
    
    if (!Config::isLoaded()) {
      
      throw new \Exception('Error reading configuration file', -10);
    }
    
    $this->isDebug = Config::get('debug') ?? $this->isDebug;
    $this->isDisabled = Config::get('disabled') ?? $this->isDisabled;
    $this->isLoaded = true;
    $this->APIKeyAnalytics = Config::get('keyAnalytics') ?? $this->APIKeyAnalytics;
    $this->APIKeyData = Config::get('keyData') ?? $this->APIKeyData;
    $this->url = Config::get('url') ?? $this->url;
  }
  
  /**
   * Інформація клієнта-юридичної особи та фізичної особи-підприємця
   *
   * @return array
   */
  private function runBusinessman():array
  {
    if ($this->isDebug) {
      trigger_error(__METHOD__." start");
    }
    
    # Відомості про справи про банкрутство (Bankruptcy Information)
    $result[YouControlTypes::bankrupt->name] = $this->getPreviousResult(YouControlTypes::bankrupt->name) ??
      $this->wsBankrupt();
    
    # НПД та суб'єкти декларування пов'язані з компанією (PEPs affiliated to the company)
    $result[YouControlTypes::companyPersons->name] = $this->getPreviousResult(YouControlTypes::companyPersons->name) ??
      $this->wsCompanyPersons();
    
    # Виконавчі провадження (Enforcement proceedings)
    $result[YouControlTypes::executive->name] = $this->getPreviousResult(YouControlTypes::executive->name) ??
      $this->wsExecutive();
    
    # ФО - Зв'язок з ФПГ (Private individual - Affiliation with FIG)
    $result[YouControlTypes::fig->name] = $this->getPreviousResult(YouControlTypes::fig->name) ?? $this->wsFig();
    
    # ФО - Перевірка паспорту (Passports check)
    $result[YouControlTypes::passports->name] = $this->getPreviousResult(YouControlTypes::passports->name) ??
      $this->wsPassports();
    
    # НПД скринінг (PEP Screening)
    # Пов'язані з шуканим НПД особи та компанії (Individuals and entities related to searched PEP)
    $result[YouControlTypes::peps->name] = $this->getPreviousResult(YouControlTypes::peps->name) ?? $this->wsPeps();
    
    # Санкції (Sanctions)
    $result[YouControlTypes::sanctions->name] = $this->getPreviousResult(YouControlTypes::sanctions->name) ??
      $this->wsSanctionsPersonal();
    
    # ФO - Податковий борг (Private individual - Tax debtors)
    $result[YouControlTypes::taxDebtor->name] = $this->getPreviousResult(YouControlTypes::taxDebtor->name) ??
      $this->wsTaxDebtor();
    
    # ФО - Терористи (Terrorists)
    $result[YouControlTypes::terrorists->name] = $this->getPreviousResult(YouControlTypes::terrorists->name) ??
      $this->wsTerrorists();
    
    # Безвісно зниклі та ті, які переховуються від органів влади (Missing or wanted persons)
    $result[YouControlTypes::wanted->name] = $this->getPreviousResult(YouControlTypes::wanted->name) ??
      $this->wsWanted();
    
    return $result;
  }
  
  /**
   * Інформація клієнта-юридичної особи та фізичної особи-підприємця
   *
   * @return array
   */
  private function runCompany():array
  {
    if ($this->isDebug) {
      trigger_error(__METHOD__." start");
    }
    
    # Відомості про справи про банкрутство (Bankruptcy Information)
    $result[YouControlTypes::bankrupt->name] = $this->getPreviousResult(YouControlTypes::bankrupt->name) ??
      $this->wsBankrupt();
    
    # НПД та суб'єкти декларування пов'язані з компанією (PEPs affiliated to the company)
    $result[YouControlTypes::companyPersons->name] = $this->getPreviousResult(YouControlTypes::companyPersons->name) ??
      $this->wsCompanyPersons();
    
    # Судові дані (Court data)
    $result[YouControlTypes::courts->name] = $this->getPreviousResult(YouControlTypes::courts->name) ??
      $this->wsCourts();
    
    # Виконавчі провадження (Enforcement proceedings)
    $result[YouControlTypes::executive->name] = $this->getPreviousResult(YouControlTypes::executive->name) ??
      $this->wsExecutive();
    
    # Детальна інформація про ФПГ (Information about FIG)
    $result[YouControlTypes::fig->name] = $this->getPreviousResult(YouControlTypes::fig->name) ?? $this->wsFigCompany();
    
    # Санкції (Sanctions)
    $result[YouControlTypes::sanctions->name] = $this->getPreviousResult(YouControlTypes::sanctions->name) ??
      $this->wsSanctions();
    
    # Наявність у компанії податкового боргу (Company's tax dept)
    $result[YouControlTypes::taxDebtor->name] = $this->getPreviousResult(YouControlTypes::taxDebtor->name) ??
      $this->wsTaxDebtorCompany();
    
    return $result;
  }
  
  /**
   * Інформація клієнта - фізичної особи
   *
   * @return array
   */
  private function runPersonal():array
  {
    if ($this->isDebug) {
      trigger_error(__METHOD__." start");
    }
    
    # ФО - Виконавчі провадження (Private individual - Enforcement proceedings)
    $result[YouControlTypes::executive->name] = $this->getPreviousResult(YouControlTypes::executive->name) ??
      $this->wsExecutivePersonal();
    
    # ФО - Зв'язок з ФПГ (Private individual - Affiliation with FIG)
    $result[YouControlTypes::fig->name] = $this->getPreviousResult(YouControlTypes::fig->name) ?? $this->wsFig();
    
    # ФО - Перевірка паспорту (Passports check)
    $result[YouControlTypes::passports->name] = $this->getPreviousResult(YouControlTypes::passports->name) ??
      $this->wsPassports();
    
    # НПД скринінг (PEP Screening)
    # Пов'язані з шуканим НПД особи та компанії (Individuals and entities related to searched PEP)
    $result[YouControlTypes::peps->name] = $this->getPreviousResult(YouControlTypes::peps->name) ?? $this->wsPeps();
    
    # Санкції (Sanctions)
    # Санкції РНБО (RNBO Sanctions)
    $result[YouControlTypes::sanctions->name] = $this->getPreviousResult(YouControlTypes::sanctions->name) ??
      $this->wsSanctionsPersonal();
    
    # ФO - Податковий борг (Private individual - Tax debtors)
    $result[YouControlTypes::taxDebtor->name] = $this->getPreviousResult(YouControlTypes::taxDebtor->name) ??
      $this->wsTaxDebtor();
    
    # ФО - Терористи (Terrorists)
    $result[YouControlTypes::terrorists->name] = $this->getPreviousResult(YouControlTypes::terrorists->name) ??
      $this->wsTerrorists();
    
    # Безвісно зниклі та ті, які переховуються від органів влади (Missing or wanted persons)
    $result[YouControlTypes::wanted->name] = $this->getPreviousResult(YouControlTypes::wanted->name) ??
      $this->wsWanted();
    
    return $result;
  }
  
  /**
   * Надіслати запит
   *
   * @param YouControlTypes $requestType
   * @param string $url
   * @param string|null $textEmpty
   *
   * @return object|array|string
   */
  private function ws(
    YouControlTypes $requestType, string $url, ?string $textEmpty = 'Пустий результат запиту'):object | array | string
  {
    # Ключ до API
    $apiKey = match ($requestType) {
      YouControlTypes::fig, YouControlTypes::sanctions => CustomerType::COMPANY === $this->subject
        ? $this->APIKeyAnalytics : $this->APIKeyData,
      default => $this->APIKeyData
    };
    
    if ('' === $apiKey) {
      if ($this->isDebug) {
        trigger_error(__METHOD__." Відсутній код доступу до API для даного запиту $requestType->value");
      }
      
      return "Відсутній ключ доступу до API";
    }
    
    # Адреса запиту
    $requestURL = $this->getURL($url, $apiKey);
    
    # Заголовки
    $requestHeader = [
      "accept: application/json",
      "Authorization: $apiKey"
    ];
    
    # Запит
    $response = Curl::exec($requestURL, $requestHeader, httpMethod: HTTPMethods::GET, timeout: 5);
    
    if ($this->isDebug) {
      trigger_error(__METHOD__." [$requestType->name]\n$requestURL\nResponse [HTTP ".Curl::$curlHttpCode
        ."]\n$response");
    }
    
    # Перетворення відповіді на об'єкт
    $json = json_decode($response);
    
    /**
     * Обробка відповіді типу<br>
     * {
     * "status": "Update in progress",
     * "resultUrl": "https://api.youscore.com.ua/v1/enforcementIndividual/64fa5965ef562e32f87ede52?skip=0&top=100"
     * }
     */
    if (202 === Curl::$curlHttpCode && !empty($json->resultUrl)) {
      $urlResult = trim($json->resultUrl);
      
      for ($i = 1; $i < 4; $i++) {
        # Адреса повторного запиту
        $requestURL = $this->getURL(trim($json->resultUrl ?? $urlResult), $apiKey);
        
        sleep(2);
        
        # Повторний запит
        $response = Curl::exec($requestURL, $requestHeader, httpMethod: HTTPMethods::GET, timeout: 5);
        
        if ($this->isDebug) {
          trigger_error(__METHOD__." [$requestType->name] Loop[$i of 3]\n$requestURL\nResponse [HTTP "
            .Curl::$curlHttpCode."]\n$response");
        }
        
        # Перетворення відповіді на об'єкт
        $json = json_decode($response);
        
        # Отримано відповідь
        if (202 !== Curl::$curlHttpCode) {
          
          break;
        }
      }
    }
    
    /**
     * Перелік можливих помилок<br>
     * {
     * "status":"Update in progress",
     * "currentDataUrl":"https://api.youscore.com.ua/v1/enforcement/31119647?Code=31119647&showCurrentData=True&Top=500&Skip=0"
     * }<br>
     * {
     * "code": "NotFound",
     * "message": "Contractor '3325819217' not found"
     * }<br>
     * {
     * "code": "InvalidParameters",
     * "error": "FirstName, LastName and MiddleName are required"
     * }
     */
    return 200 === Curl::$curlHttpCode
      ? (JSON_ERROR_NONE === json_last_error() ? (empty((array)$json) && 'string' === gettype($textEmpty) ? $textEmpty
        : $json) : json_last_error_msg())
      : trim($json->error ?? $json->message ??
        $json->status ?? "Помилка виконання запиту".(Curl::$curlHttpCode ? ' ['.Curl::$curlHttpCode.']' : ''));
  }
  
  /**
   * Відомості про справи про банкрутство (Bankruptcy Information)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%92%D1%96%D0%B4%D0%BE%D0%BC%D0%BE%D1%81%D1%82%D1%96%20%D0%BF%D1%80%D0%BE%20%D1%81%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%20%D0%BF%D1%80%D0%BE%20%D0%B1%D0%B0%D0%BD%D0%BA%D1%80%D1%83%D1%82%D1%81%D1%82%D0%B2%D0%BE%20(Bankruptcy%20Information)/get_v1_secou
   */
  private function wsBankrupt():array | string
  {
    /**
     * Справи відсутні
     * []
     */
    return $this->ws(YouControlTypes::bankrupt, "v1/secou?contractorCode=$this->code", null);
  }
  
  /**
   * НПД та суб'єкти декларування пов'язані з компанією (PEPs affiliated to the company)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9D%D0%9F%D0%94%20%D1%82%D0%B0%20%D1%81%D1%83%D0%B1%E2%80%99%D1%94%D0%BA%D1%82%D0%B8%20%D0%B4%D0%B5%D0%BA%D0%BB%D0%B0%D1%80%D1%83%D0%B2%D0%B0%D0%BD%D0%BD%D1%8F%20%D0%BF%D0%BE%D0%B2%E2%80%99%D1%8F%D0%B7%D0%B0%D0%BD%D1%96%20%D0%B7%20%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D1%96%D1%94%D1%8E%20(PEPs%20affiliated%20to%20the%20company)/get_v1_companyPersons_relations
   */
  private function wsCompanyPersons():object | string
  {
    /**
     * {
     * "peps":[],
     * "declarants":[]
     * }
     */
    return $this->ws(YouControlTypes::companyPersons, "v1/companyPersons/relations?contractorCode=$this->code");
  }
  
  /**
   * Судові дані (Court data)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A1%D1%83%D0%B4%D0%BE%D0%B2%D1%96%20%D0%B4%D0%B0%D0%BD%D1%96%20(Court%20data)/get_v1_courtCaseGroup__contractorCode_
   */
  private function wsCourts():object | string
  {
    /**
     * {
     * "totalResults": 0,
     * "nextPageUrl": null,
     * "results": []
     * }
     */
    return $this->ws(YouControlTypes::courts, "v1/courtCaseGroup/$this->code?showCurrentData=true");
  }
  
  /**
   * Виконавчі провадження (Enforcement proceedings)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%92%D0%B8%D0%BA%D0%BE%D0%BD%D0%B0%D0%B2%D1%87%D1%96%20%D0%BF%D1%80%D0%BE%D0%B2%D0%B0%D0%B4%D0%B6%D0%B5%D0%BD%D0%BD%D1%8F%20(Enforcement%20proceedings)/get_v1_enforcement__contractorCode_
   */
  private function wsExecutive():object | string
  {
    /**
     * {
     * "totalResults":0,
     * "nextPageUrl":null,
     * "results":[]
     * }
     */
    return $this->ws(YouControlTypes::executive, "v1/enforcement/$this->code?showCurrentData=true");
  }
  
  /**
   * Фізичні особи - Виконавчі провадження (Private individual - Enforcement proceedings)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%92%D0%B8%D0%BA%D0%BE%D0%BD%D0%B0%D0%B2%D1%87%D1%96%20%D0%BF%D1%80%D0%BE%D0%B2%D0%B0%D0%B4%D0%B6%D0%B5%D0%BD%D0%BD%D1%8F%20(Private%20individual%20-%20Enforcement%20proceedings)/get_v1_enforcementIndividual
   */
  private function wsExecutivePersonal():object | string
  {
    $query = "Name=".urlencode($this->firstName);
    $query .= "&Surname=".urlencode($this->lastName);
    $query .= '' === $this->middleName ? '' : "&MiddleName=".urlencode($this->middleName);
    $query .= '' === $this->birthday ? '' : "&Birthday=".urlencode($this->birthday);
    
    /**
     * {
     * "registryUpdateTime": "2023-09-07T23:22:09.86Z",
     * "totalResults": 0,
     * "nextPageUrl": null,
     * "results": []
     * }
     */
    return $this->ws(YouControlTypes::executive, "v1/enforcementIndividual?$query");
  }
  
  /**
   * ФО - Зв'язок з ФПГ (Private individual - Affiliation with FIG)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%97%D0%B2%E2%80%99%D1%8F%D0%B7%D0%BE%D0%BA%20%D0%B7%20%D1%84%D1%96%D0%BD%D0%B0%D0%BD%D1%81%D0%BE%D0%B2%D0%BE-%D0%BF%D1%80%D0%BE%D0%BC%D0%B8%D1%81%D0%BB%D0%BE%D0%B2%D0%B8%D0%BC%D0%B8%20%D0%B3%D1%80%D1%83%D0%BF%D0%B0%D0%BC%D0%B8%20(Private%20individual%20-%20Affiliation%20with%20financial-industrial%20groups)/get_v1_individualsFigCompanies
   */
  private function wsFig():object | array | string
  {
    /**
     * {
     * "registryUpdateTime": "2023-09-08T12:31:44.414Z",
     * "result": []
     * }
     */
    return $this->ws(YouControlTypes::fig, "v1/individualsFigCompanies?".$this->getQuery());
  }
  
  /**
   * Приналежність до ФПГ (Affiliation with FIG)
   *
   * @return object|array|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9F%D1%80%D0%B8%D0%BD%D0%B0%D0%BB%D0%B5%D0%B6%D0%BD%D1%96%D1%81%D1%82%D1%8C%20%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D1%96%D1%97%20%D0%B4%D0%BE%20%D1%84%D1%96%D0%BD%D0%B0%D0%BD%D1%81%D0%BE%D0%B2%D0%BE-%D0%BF%D1%80%D0%BE%D0%BC%D0%B8%D1%81%D0%BB%D0%BE%D0%B2%D0%B8%D1%85%20%D0%B3%D1%80%D1%83%D0%BF%20(%D0%A4%D0%9F%D0%93)%20(Affiliation%20with%20financial-industrial%20groups%20(FIG))/get_v1_fig
   */
  private function wsFigCompany():object | array | string
  {
    /**
     * Інформація відсутня
     * []
     */
    return $this->ws(YouControlTypes::fig, "v1/fig?contractorCode=$this->code", null);
    
    # Перевірка тимчасово вимкнена
    /*
    if ('string' === gettype($result) || empty($result)) {
      
      return $result;
    }
    
    
    # Детальна інформація про ФПГ / Information about FIG
    /*foreach ($result as $fig) {
      $id = (int)($fig->id ?? '');
      
      if (0 >= $id) {
        
        continue;
      }
      
      $fig->details = $this->ws(YouControlTypes::fig, "v1/fig/$id?");
    }
    
    return $result;*/
  }
  
  /**
   * Фізичні особи - Перевірка паспорту (Passports check)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%9F%D0%B5%D1%80%D0%B5%D0%B2%D1%96%D1%80%D0%BA%D0%B0%20%D0%BF%D0%B0%D1%81%D0%BF%D0%BE%D1%80%D1%82%D1%83%20(Passports%20check)/get_v1_passports
   */
  private function wsPassports():object | string
  {
    $query = 'number='.urlencode($this->documentNumber);
    $query .= '' === $this->documentSeries ? '' : '&series='.urlencode($this->documentSeries);
    
    /**
     * HTTP 404 - заборгованість відсутня<br>
     * {
     * "code":"NotFound",
     * "message":"No passports were found amongst neither invalid nor stolen or lost passports for passport with series МН and number 648331"
     * }<br>
     * HTTP 200 - Можливій збіг<br>
     * invalidPassports - блок відповіді реєстра ДМС<br>
     * stolenOrLostPassports - блок відповіді реєстра МВС<br>
     * {
     *  "invalidPassports": [
     *    {
     *      "number": "570180",
     *      "series": "АН",
     *      "editDate": "2016-06-18T15:14:42+03:00",
     *      "status": "недійсний",
     *      "actualDate": "2019-06-12T11:12:13.884+03:00"
     *    }
     *  ],
     * "stolenOrLostPassports": [
     *    {
     *      "series": "АН",
     *      "number": "570180",
     *      "regionalDepartment": "ІНДУСТРІАЛЬНЕ ВІДДІЛЕННЯ ПОЛІЦІЇ ДНІПРОВСЬКОГО ВІДДІЛУ ГУНП В ДНІПРОПЕТРОВСЬКІЙ ОБЛ.",
     *      "theftDate": "2013-07-26T00:00:00+03:00",
     *      "insertDate": "2013-06-13T00:00:00+03:00",
     *      "actualDate": "2019-06-13T00:00:00+03:00"
     *    }
     *  ]
     * }
     */
    $result = $this->ws(YouControlTypes::passports, "v1/passports?$query");
    
    return 404 === Curl::$curlHttpCode && 'string' === gettype($result) ? new \stdClass() : $result;
  }
  
  /**
   * НПД скринінг (PEP Screening)<br>
   * Пов'язані з шуканим НПД особи та компанії (Individuals and entities related to searched PEP)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9D%D0%9F%D0%94%20%D1%81%D0%BA%D1%80%D0%B8%D0%BD%D1%96%D0%BD%D0%B3%20(PEP%20Screening)/get_v1_peps
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9D%D0%9F%D0%94%20%D1%81%D0%BA%D1%80%D0%B8%D0%BD%D1%96%D0%BD%D0%B3%20(PEP%20Screening)/get_v1_peps_related
   */
  private function wsPeps():object | string
  {
    if ('' === $this->middleName) {
      
      return (object)[
        "isPep" => false,
        "isRelatedToPep" => false,
        "comment" => "Відсутнє по-батькові, перевірка не проводилась"
      ];
    }
    
    # Строка з даними контрагента
    $query = $this->getQuery();
    
    /**
     * НПД скринінг (PEP Screening)<br>
     * {
     * "searchedName": "Шевчук Олександр Іванович",
     * "isPep": false,
     * "isRelatedToPep": false,
     * "numberOfMatches": 0,
     * "pepMatches": [],
     * "relatedToPepMatches": []
     * }
     */
    $result = $this->ws(YouControlTypes::peps, "v1/peps?$query");
    
    if ('string' === gettype($result) || !(($result->isPep ?? false) || ($result->isRelatedToPep ?? false))) {
      
      return $result;
    }
    
    /**
     * Пов'язані з шуканим НПД особи та компанії (Individuals and entities related to searched PEP)<br>
     * {
     * "searchedName": "Василишина Таїса Василівна",
     * "relatedPersons": [],
     * "relatedLegalEntities": []
     * }
     */
    $result->relatedPersons = $this->ws(YouControlTypes::relatedPersons, "v1/peps/related?$query");
    
    return $result;
  }
  
  /**
   * Санкції (Sanctions)
   *
   * @return object|array|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A1%D0%B0%D0%BD%D0%BA%D1%86%D1%96%D1%97%20(Sanctions)/get_v1_sanctions
   */
  private function wsSanctions():object | array | string
  {
    /**
     * Санкції відсутні<br>
     * []
     */
    return $this->ws(YouControlTypes::sanctions, "v1/sanctions?contractorCode=$this->code", null);
  }
  
  /**
   * Санкції (Sanctions)<br>
   * ФО - Санкції (Sanctions screening)
   *
   * @return object|array|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%A1%D0%B0%D0%BD%D0%BA%D1%86%D1%96%D1%97%20(Sanctions%20screening)/get_v1_individualsGlobalSanctionsLists
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%A1%D0%B0%D0%BD%D0%BA%D1%86%D1%96%D1%97%20(Sanctions%20screening)/get_v1_individualsRnboSanctions
   */
  private function wsSanctionsPersonal():object | array | string
  {
    $query = $this->getQuery();
    
    /**
     * Перевірка Міжнародних санкцій / Global Sanctions Lists Screening<br>
     * {
     * "issls": false,
     * "searchedName": "Шевчук Олександр Миколайович",
     * "numberOfMatches": 0,
     * "results": []
     * }
     */
    $resultSanctions = $this->ws(YouControlTypes::sanctions, "v1/individualsGlobalSanctionsLists?$query");
    
    # Помилка отримання даних
    if ('string' === gettype($resultSanctions)) {
      
      return $resultSanctions;
    }
    
    /**
     * Санкції РНБО (RNBO Sanctions)<br>
     * {
     *  "registryUpdateTime": "2023-09-08T13:18:52.385Z",
     *  "result": []
     *  }
     */
    $result = $this->ws(YouControlTypes::sanctions, "v1/individualsRnboSanctions?$query");
    
    $resultSanctions->rnbo = $result;
    
    return $resultSanctions;
  }
  
  /**
   * ФO - Податковий борг (Private individual - Tax debtors)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%91%D0%BE%D1%80%D0%B6%D0%BD%D0%B8%D0%BA%D0%B8%20(Private%20individual%20-%20Debtors)/get_v1_individualsTaxDebtors
   */
  private function wsTaxDebtor():object | string
  {
    /**
     * {
     * "individualsTaxDebtorsRegistryUpdateTime": "2022-02-23T10:00:00Z",
     * "result": []
     * }
     */
    return $this->ws(YouControlTypes::taxDebtor, "v1/individualsTaxDebtors?".$this->getQuery());
  }
  
  /**
   * Наявність у компанії податкового боргу (Company's tax dept)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9F%D0%BE%D0%B4%D0%B0%D1%82%D0%BA%D0%BE%D0%B2%D1%96%20%D0%B4%D0%B0%D0%BD%D1%96%20(Tax%20data)/get_v1_taxDebt__contractorCode_
   */
  private function wsTaxDebtorCompany():object | string
  {
    /**
     * HTTP 404 - заборгованість відсутня<br>
     * {
     * "code": "NotFound",
     * "message": "No tax debt info for contractor '43745739' found"
     * }<br>
     * HTTP 200 - наявна заборгованість<br>
     * {
     * "debt": 40972.08,
     * "actualDate": "2021-05-01T00:00:00+03:00"
     * }
     */
    $result = $this->ws(YouControlTypes::taxDebtor, "v1/taxDebt/$this->code?");
    
    return 404 === Curl::$curlHttpCode && 'string' === gettype($result) ? new \stdClass() : $result;
  }
  
  /**
   * ФО - Терористи (Terrorists)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%A2%D0%B5%D1%80%D0%BE%D1%80%D0%B8%D1%81%D1%82%D0%B8%20(Terrorists)/get_v1_individualsDsfmuTerrorists
   */
  private function wsTerrorists():object | string
  {
    /**
     * {
     * "registryUpdateTime": "2022-02-23T10:00:00Z",
     * "result": []
     * }
     */
    return $this->ws(YouControlTypes::terrorists, "v1/individualsDsfmuTerrorists?".$this->getQuery());
  }
  
  /**
   * Безвісно зниклі та ті, які переховуються від органів влади (Missing or wanted persons)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%91%D0%B5%D0%B7%D0%B2%D1%96%D1%81%D0%BD%D0%BE%20%D0%B7%D0%BD%D0%B8%D0%BA%D0%BB%D1%96%20%D1%82%D0%B0%20%D1%82%D1%96%2C%20%D1%8F%D0%BA%D1%96%20%D0%BF%D0%B5%D1%80%D0%B5%D1%85%D0%BE%D0%B2%D1%83%D1%8E%D1%82%D1%8C%D1%81%D1%8F%20%D0%B2%D1%96%D0%B4%20%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D1%96%D0%B2%20%D0%B2%D0%BB%D0%B0%D0%B4%D0%B8%20(Missing%20or%20wanted%20persons)/get_v1_wantedOrDisappearedPersons
   */
  private function wsWanted():object | string
  {
    # Строка з даними контрагента
    $query = $this->getQuery();
    $query .= '' === $this->birthday ? '' : "&BirthDate=".urlencode($this->birthday);
    
    /**
     * {
     * "disappearedPersonsRegistryUpdateTime": "2019-10-14T23:32:00Z",
     * "wantedPersonsRegistryUpdateTime": "2019-10-14T23:34:00Z",
     * "disappearedPersons": [],
     * "wantedPersons": []
     * }
     */
    return $this->ws(YouControlTypes::wanted, "v1/wantedOrDisappearedPersons?$query");
    
    # Перевірка вимкнена
    /*
    if ('string' === gettype($result) || 200 !== Curl::$curlHttpCode
      || empty($result->disappearedPersons)
      && empty($result->wantedPersons)) {
      
      return $result;
    }
    
    
    # Пошук фото в реєстрі осіб, зниклих безвісти
    foreach ($result->disappearedPersons as $person) {
      foreach ($person->photos as $photo) {
        usleep(300);
        $this->getPhoto($photo);
      }
    }
    
    # Пошук фото в реєстрі осіб, які переховуються від органів влади
    foreach ($result->wantedPersons as $person) {
      foreach ($person->photos as $photo) {
        usleep(300);
        $this->getPhoto($photo);
      }
    }
    
    return $result;*/
  }
}
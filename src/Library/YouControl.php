<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\YouControlEnum;

final class YouControl
{
  /**
   * Дата народження контрагента
   *
   * @var string
   */
  public string $birthday = '';
  
  /**
   * Код контрагента
   *
   * @var string
   */
  public string $code = '';
  
  /**
   * Режим налаштування
   *
   * @var bool
   */
  public bool $debug = false;
  
  /**
   * Стан сервісу
   * true - відключений
   * false - працює
   */
  public static bool $disabled = true;
  
  /**
   * Номер документа
   *
   * @var string
   */
  public string $documentNumber = '';
  
  /**
   * Серія документа
   *
   * @var string
   */
  public string $documentSeries = '';
  
  /**
   * Ім'я контрагента
   *
   * @var string
   */
  public string $firstName = '';
  
  /**
   * Прізвище контрагента
   *
   * @var string
   */
  public string $lastName = '';
  
  /**
   * По батькові контрагента
   *
   * @var string
   */
  public string $middleName = '';
  
  /**
   * Тип контрагента
   *
   * @var string
   */
  public string $subject;
  
  /**
   * Ключ API "Аналітика"
   *
   * @var string
   */
  private string $APIKeyAnalytics = '';
  
  /**
   * Ключ API "Дані"
   *
   * @var string
   */
  private string $APIKeyData = '';
  
  /**
   * Адреса API
   *
   * @var string
   */
  private string $url = "https://api.youscore.com.ua/";
  
  /**
   * Попередній результат
   *
   * @var mixed|array
   */
  private mixed $result = [];
  
  public function __construct(array $request)
  {
    if ($this->debug) {
      trigger_error(__METHOD__." Request\n".json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    $settings = File::parse(['/json/you-control.json']);
    
    $this->APIKeyAnalytics = $settings->keyAnalytics ?? $this->APIKeyAnalytics;
    $this->APIKeyData = $settings->keyData ?? $this->APIKeyData;
    
    # Тип контрагента
    $this->subject = mb_convert_case(trim($request['subject'] ?? ''), MB_CASE_UPPER);
    
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
    $this->result = $request['result'] ?? $this->result;
  }
  
  /**
   * Запит до API YouControl
   *
   * @return string
   */
  public function sendRequest():string
  {
    
    return self::$disabled
      ? ''
      : (string)json_encode(match ($this->subject) {
        'СУБЪЕКТ_ФЛ' => self::_runPersonal(),
        'СУБЪЕКТ_ФОП' => self::_runBusinessman(),
        'СУБЪЕКТ_ЮЛ' => self::_runCompany(),
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
  private function _getPhoto(object $photo):void
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
    
    # Визначення типу зображення
    foreach ($http_response_header as $header) {
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
  
  private function _getPreviousResult(string $nameCheck):mixed
  {
    if ($this->debug) {
      trigger_error(__METHOD__." Check [$nameCheck] isset[".isset($this->result->{$nameCheck}).'] type ['
        .gettype($this->result->{"$nameCheck"} ?? null).']');
    }
    
    return isset($this->result->{$nameCheck}) && 'string' !== gettype($this->result->{$nameCheck})
      ? $this->result->{$nameCheck} : null;
  }
  
  /**
   * Формування строки запиту
   *
   * @return string
   */
  private function _getQuery():string
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
  private function _getURL(string $url, string $apiKey):string
  {
    $_url = str_starts_with(strtolower($url), 'http') ? '' : $this->url;
    $_url .= $url;
    $_url .= false === mb_stripos($url, '?') ? '?' : '&';
    $_url .= "apiKey=$apiKey";
    
    return $_url;
  }
  
  /**
   * Інформація клієнта-юридичної особи та фізичної особи-підприємця
   *
   * @return array
   */
  private function _runBusinessman():array
  {
    # Відомості про справи про банкрутство (Bankruptcy Information)
    $result[YouControlEnum::bankrupt->name] = self::_getPreviousResult(YouControlEnum::bankrupt->name)
      ? : self::_wsBankrupt();
    
    # НПД та суб'єкти декларування пов'язані з компанією (PEPs affiliated to the company)
    $result[YouControlEnum::companyPersons->name] = self::_getPreviousResult(YouControlEnum::companyPersons->name)
      ? : self::_wsCompanyPersons();
    
    # Виконавчі провадження (Enforcement proceedings)
    $result[YouControlEnum::executive->name] = self::_getPreviousResult(YouControlEnum::executive->name)
      ? : self::_wsExecutive();
    
    # ФО - Зв'язок з ФПГ (Private individual - Affiliation with FIG)
    $result[YouControlEnum::fig->name] = self::_getPreviousResult(YouControlEnum::fig->name) ? : self::_wsFig();
    
    # ФО - Перевірка паспорту (Passports check)
    $result[YouControlEnum::passports->name] = self::_getPreviousResult(YouControlEnum::passports->name)
      ? : self::_wsPassports();
    
    # НПД скринінг (PEP Screening)
    # Пов'язані з шуканим НПД особи та компанії (Individuals and entities related to searched PEP)
    $result[YouControlEnum::peps->name] = self::_getPreviousResult(YouControlEnum::peps->name) ? : self::_wsPeps();
    
    # Санкції (Sanctions)
    $result[YouControlEnum::sanctions->name] = self::_getPreviousResult(YouControlEnum::sanctions->name)
      ? : self::_wsSanctions();
    
    # ФO - Податковий борг (Private individual - Tax debtors)
    $result[YouControlEnum::taxDebtor->name] = self::_getPreviousResult(YouControlEnum::taxDebtor->name)
      ? : self::_wsTaxDebtor();
    
    # ФО - Терористи (Terrorists)
    $result[YouControlEnum::terrorists->name] = self::_getPreviousResult(YouControlEnum::terrorists->name)
      ? : self::_wsTerrorists();
    
    # Безвісно зниклі та ті, які переховуються від органів влади (Missing or wanted persons)
    $result[YouControlEnum::wanted->name] = self::_getPreviousResult(YouControlEnum::wanted->name)
      ? : self::_wsWanted();
    
    return $result;
  }
  
  /**
   * Інформація клієнта-юридичної особи та фізичної особи-підприємця
   *
   * @return array
   */
  private function _runCompany():array
  {
    # Відомості про справи про банкрутство (Bankruptcy Information)
    $result[YouControlEnum::bankrupt->name] = self::_getPreviousResult(YouControlEnum::bankrupt->name)
      ? : self::_wsBankrupt();
    
    # НПД та суб'єкти декларування пов'язані з компанією (PEPs affiliated to the company)
    $result[YouControlEnum::companyPersons->name] = self::_getPreviousResult(YouControlEnum::companyPersons->name)
      ? : self::_wsCompanyPersons();
    
    # Судові дані (Court data)
    $result[YouControlEnum::courts->name] = self::_getPreviousResult(YouControlEnum::courts->name)
      ? : self::_wsCourts();
    
    # Виконавчі провадження (Enforcement proceedings)
    $result[YouControlEnum::executive->name] = self::_getPreviousResult(YouControlEnum::executive->name)
      ? : self::_wsExecutive();
    
    # Детальна інформація про ФПГ (Information about FIG)
    $result[YouControlEnum::fig->name] = self::_getPreviousResult(YouControlEnum::fig->name) ? : self::_wsFigCompany();
    
    # Санкції (Sanctions)
    $result[YouControlEnum::sanctions->name] = self::_getPreviousResult(YouControlEnum::sanctions->name)
      ? : self::_wsSanctions();
    
    # Наявність у компанії податкового боргу (Company's tax dept)
    $result[YouControlEnum::taxDebtor->name] = self::_getPreviousResult(YouControlEnum::taxDebtor->name)
      ? : self::_wsTaxDebtorCompany();
    
    return $result;
  }
  
  /**
   * Інформація клієнта - фізичної особи
   *
   * @return array
   */
  private function _runPersonal():array
  {
    # ФО - Виконавчі провадження (Private individual - Enforcement proceedings)
    $result[YouControlEnum::executive->name] = self::_getPreviousResult(YouControlEnum::executive->name)
      ? : self::_wsExecutivePersonal();
    
    # ФО - Зв'язок з ФПГ (Private individual - Affiliation with FIG)
    $result[YouControlEnum::fig->name] = self::_getPreviousResult(YouControlEnum::fig->name) ? : self::_wsFig();
    
    # ФО - Перевірка паспорту (Passports check)
    $result[YouControlEnum::passports->name] = self::_getPreviousResult(YouControlEnum::passports->name)
      ? : self::_wsPassports();
    
    # НПД скринінг (PEP Screening)
    # Пов'язані з шуканим НПД особи та компанії (Individuals and entities related to searched PEP)
    $result[YouControlEnum::peps->name] = self::_getPreviousResult(YouControlEnum::peps->name) ? : self::_wsPeps();
    
    # Санкції (Sanctions)
    # Санкції РНБО (RNBO Sanctions)
    $result[YouControlEnum::sanctions->name] = self::_getPreviousResult(YouControlEnum::sanctions->name)
      ? : self::_wsSanctionsPersonal();
    
    # ФO - Податковий борг (Private individual - Tax debtors)
    $result[YouControlEnum::taxDebtor->name] = self::_getPreviousResult(YouControlEnum::taxDebtor->name)
      ? : self::_wsTaxDebtor();
    
    # ФО - Терористи (Terrorists)
    $result[YouControlEnum::terrorists->name] = self::_getPreviousResult(YouControlEnum::terrorists->name)
      ? : self::_wsTerrorists();
    
    # Безвісно зниклі та ті, які переховуються від органів влади (Missing or wanted persons)
    $result[YouControlEnum::wanted->name] = self::_getPreviousResult(YouControlEnum::wanted->name)
      ? : self::_wsWanted();
    
    return $result;
  }
  
  /**
   * Надіслати запит
   *
   * @param YouControlEnum $requestType
   * @param string $url
   * @param string|null $textEmpty
   *
   * @return object|array|string
   */
  private function _ws(
    YouControlEnum $requestType, string $url, ?string $textEmpty = 'Пустий результат запиту'):object | array | string
  {
    # Ключ до API
    $apiKay = match ($requestType) {
      YouControlEnum::fig, YouControlEnum::sanctions => 'СУБЪЕКТ_ЮЛ' === $this->subject ? $this->APIKeyAnalytics
        : $this->APIKeyData,
      default => $this->APIKeyData
    };
    
    # Адреса запиту
    $requestURL = self::_getURL($url, $apiKay);
    
    # Заголовки
    $requestHeader = [
      "accept: application/json",
      "Authorization: $apiKay"
    ];
    
    # Запит
    $response = Curl::exec($requestURL, $requestHeader, false, '', '', 0, false, 5);
    
    if ($this->debug) {
      trigger_error(__METHOD__." [$requestType->name]\n$requestURL\nResponse [HTTP ".Curl::$curlHttpCode
        ."]\n$response");
    }
    
    # Перетворення відповіді на об'єкт
    $json = json_decode($response);
    
    /**
     * Обробка відповіді типу
     * {
     * "status": "Update in progress",
     * "resultUrl": "https://api.youscore.com.ua/v1/enforcementIndividual/64fa5965ef562e32f87ede52?skip=0&top=100"
     * }
     */
    if (202 === Curl::$curlHttpCode && !empty($json->resultUrl)) {
      $urlResult = trim($json->resultUrl);
      
      for ($i = 1; $i <= 4; $i++) {
        # Адреса повторного запиту
        $requestURL = self::_getURL(trim($json->resultUrl ?? $urlResult), $apiKay);
        
        sleep(1);
        
        # Повторний запит
        $response = Curl::exec($requestURL, $requestHeader, false, '', '', 0, false, 5);
        
        if ($this->debug) {
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
     * Перелік можливих помилок
     * {
     * "status":"Update in progress",
     * "currentDataUrl":"https://api.youscore.com.ua/v1/enforcement/31119647?Code=31119647&showCurrentData=True&Top=500&Skip=0"
     * }
     * {
     * "code": "NotFound",
     * "message": "Contractor '3325819217' not found"
     * }
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
  private function _wsBankrupt():array | string
  {
    /**
     * Справи відсутні
     * []
     */
    return self::_ws(YouControlEnum::bankrupt, "v1/secou?contractorCode=$this->code", null);
  }
  
  /**
   * НПД та суб'єкти декларування пов'язані з компанією (PEPs affiliated to the company)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9D%D0%9F%D0%94%20%D1%82%D0%B0%20%D1%81%D1%83%D0%B1%E2%80%99%D1%94%D0%BA%D1%82%D0%B8%20%D0%B4%D0%B5%D0%BA%D0%BB%D0%B0%D1%80%D1%83%D0%B2%D0%B0%D0%BD%D0%BD%D1%8F%20%D0%BF%D0%BE%D0%B2%E2%80%99%D1%8F%D0%B7%D0%B0%D0%BD%D1%96%20%D0%B7%20%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D1%96%D1%94%D1%8E%20(PEPs%20affiliated%20to%20the%20company)/get_v1_companyPersons_relations
   */
  private function _wsCompanyPersons():object | string
  {
    /**
     * {
     * "peps":[],
     * "declarants":[]
     * }
     */
    return self::_ws(YouControlEnum::companyPersons, "v1/companyPersons/relations?contractorCode=$this->code");
  }
  
  /**
   * Судові дані (Court data)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A1%D1%83%D0%B4%D0%BE%D0%B2%D1%96%20%D0%B4%D0%B0%D0%BD%D1%96%20(Court%20data)/get_v1_courtCaseGroup__contractorCode_
   */
  private function _wsCourts():object | string
  {
    /**
     * {
     * "totalResults": 0,
     * "nextPageUrl": null,
     * "results": []
     * }
     */
    return self::_ws(YouControlEnum::courts, "v1/courtCaseGroup/$this->code?showCurrentData=true");
  }
  
  /**
   * Виконавчі провадження (Enforcement proceedings)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%92%D0%B8%D0%BA%D0%BE%D0%BD%D0%B0%D0%B2%D1%87%D1%96%20%D0%BF%D1%80%D0%BE%D0%B2%D0%B0%D0%B4%D0%B6%D0%B5%D0%BD%D0%BD%D1%8F%20(Enforcement%20proceedings)/get_v1_enforcement__contractorCode_
   */
  private function _wsExecutive():object | string
  {
    /**
     * {
     * "totalResults":0,
     * "nextPageUrl":null,
     * "results":[]
     * }
     */
    return self::_ws(YouControlEnum::executive, "v1/enforcement/$this->code?showCurrentData=true");
  }
  
  /**
   * Фізичні особи - Виконавчі провадження (Private individual - Enforcement proceedings)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%92%D0%B8%D0%BA%D0%BE%D0%BD%D0%B0%D0%B2%D1%87%D1%96%20%D0%BF%D1%80%D0%BE%D0%B2%D0%B0%D0%B4%D0%B6%D0%B5%D0%BD%D0%BD%D1%8F%20(Private%20individual%20-%20Enforcement%20proceedings)/get_v1_enforcementIndividual
   */
  private function _wsExecutivePersonal():object | string
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
    return self::_ws(YouControlEnum::executive, "v1/enforcementIndividual?$query");
  }
  
  /**
   * ФО - Зв'язок з ФПГ (Private individual - Affiliation with FIG)
   *
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%97%D0%B2%E2%80%99%D1%8F%D0%B7%D0%BE%D0%BA%20%D0%B7%20%D1%84%D1%96%D0%BD%D0%B0%D0%BD%D1%81%D0%BE%D0%B2%D0%BE-%D0%BF%D1%80%D0%BE%D0%BC%D0%B8%D1%81%D0%BB%D0%BE%D0%B2%D0%B8%D0%BC%D0%B8%20%D0%B3%D1%80%D1%83%D0%BF%D0%B0%D0%BC%D0%B8%20(Private%20individual%20-%20Affiliation%20with%20financial-industrial%20groups)/get_v1_individualsFigCompanies
   */
  private function _wsFig():object | array | string
  {
    /**
     * {
     * "registryUpdateTime": "2023-09-08T12:31:44.414Z",
     * "result": []
     * }
     */
    return self::_ws(YouControlEnum::fig, "v1/individualsFigCompanies?".self::_getQuery());
  }
  
  /**
   * Приналежність до ФПГ (Affiliation with FIG)
   *
   * @return object|array|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9F%D1%80%D0%B8%D0%BD%D0%B0%D0%BB%D0%B5%D0%B6%D0%BD%D1%96%D1%81%D1%82%D1%8C%20%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D1%96%D1%97%20%D0%B4%D0%BE%20%D1%84%D1%96%D0%BD%D0%B0%D0%BD%D1%81%D0%BE%D0%B2%D0%BE-%D0%BF%D1%80%D0%BE%D0%BC%D0%B8%D1%81%D0%BB%D0%BE%D0%B2%D0%B8%D1%85%20%D0%B3%D1%80%D1%83%D0%BF%20(%D0%A4%D0%9F%D0%93)%20(Affiliation%20with%20financial-industrial%20groups%20(FIG))/get_v1_fig
   */
  private function _wsFigCompany():object | array | string
  {
    /**
     * Інформація відсутня
     * []
     */
    $result = self::_ws(YouControlEnum::fig, "v1/fig?contractorCode=$this->code", null);
    
    if ('string' === gettype($result) || empty($result)) {
      
      return $result;
    }
    
    # Детальна інформація про ФПГ / Information about FIG
    /*foreach ($result as $fig) {
      $id = (int)($fig->id ?? '');
      
      if (0 >= $id) {
        
        continue;
      }
      
      $fig->details = self::_ws(YouControlEnum::fig, "v1/fig/$id?");
    }*/
    
    return $result;
  }
  
  /**
   * Фізичні особи - Перевірка паспорту (Passports check)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%9F%D0%B5%D1%80%D0%B5%D0%B2%D1%96%D1%80%D0%BA%D0%B0%20%D0%BF%D0%B0%D1%81%D0%BF%D0%BE%D1%80%D1%82%D1%83%20(Passports%20check)/get_v1_passports
   */
  private function _wsPassports():object | string
  {
    $query = 'number='.urlencode($this->documentNumber);
    $query .= '' === $this->documentSeries ? '' : '&series='.urlencode($this->documentSeries);
    
    /**
     * HTTP 404 - заборгованість відсутня
     * {
     * "code":"NotFound",
     * "message":"No passports were found amongst neither invalid nor stolen or lost passports for passport with series МН and number 648331"
     * }
     * HTTP 200 - Можливій збіг
     * invalidPassports - блок відповіді реєстра ДМС
     * stolenOrLostPassports - блок відповіді реєстра МВС
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
    $result = self::_ws(YouControlEnum::passports, "v1/passports?$query");
    
    return 404 === Curl::$curlHttpCode && 'string' === gettype($result) ? new \stdClass() : $result;
  }
  
  /**
   * НПД скринінг (PEP Screening)<br>
   * Пов'язані з шуканим НПД особи та компанії (Individuals and entities related to searched PEP)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9D%D0%9F%D0%94%20%D1%81%D0%BA%D1%80%D0%B8%D0%BD%D1%96%D0%BD%D0%B3%20(PEP%20Screening)%20%E2%80%94%20beta-testing/get_v1_peps
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9D%D0%9F%D0%94%20%D1%81%D0%BA%D1%80%D0%B8%D0%BD%D1%96%D0%BD%D0%B3%20(PEP%20Screening)%20%E2%80%94%20beta-testing/get_v1_peps_related
   */
  private function _wsPeps():object | string
  {
    # Строка з даними контрагента
    $query = self::_getQuery();
    
    /**
     * НПД скринінг (PEP Screening)
     * {
     * "searchedName": "Шевчук Олександр Іванович",
     * "isPep": false,
     * "isRelatedToPep": false,
     * "numberOfMatches": 0,
     * "pepMatches": [],
     * "relatedToPepMatches": []
     * }
     */
    $result = self::_ws(YouControlEnum::peps, "v1/peps?$query");
    
    if ('string' === gettype($result) || !(($result->isPep ?? false) || ($result->isRelatedToPep ?? false))) {
      
      return $result;
    }
    
    /**
     * Пов'язані з шуканим НПД особи та компанії (Individuals and entities related to searched PEP)
     * {
     * "searchedName": "Василишина Таїса Василівна",
     * "relatedPersons": [],
     * "relatedLegalEntities": []
     * }
     */
    $result->relatedPersons = self::_ws(YouControlEnum::relatedPersons, "v1/peps/related?$query");
    
    return $result;
  }
  
  /**
   * Санкції (Sanctions)<br>
   *
   * @return object|array|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A1%D0%B0%D0%BD%D0%BA%D1%86%D1%96%D1%97%20(Sanctions)/get_v1_sanctions
   */
  private function _wsSanctions():object | array | string
  {
    /**
     * Санкції відсутні
     * []
     */
    return self::_ws(YouControlEnum::sanctions, "v1/sanctions?contractorCode=$this->code", null);
  }
  
  /**
   * Санкції (Sanctions)<br>
   * ФО - Санкції (Sanctions screening)
   *
   * @return object|array|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%A1%D0%B0%D0%BD%D0%BA%D1%86%D1%96%D1%97%20(Sanctions%20screening)/get_v1_individualsGlobalSanctionsLists
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%A1%D0%B0%D0%BD%D0%BA%D1%86%D1%96%D1%97%20(Sanctions%20screening)/get_v1_individualsRnboSanctions
   */
  private function _wsSanctionsPersonal():object | array | string
  {
    $query = self::_getQuery();
    
    /**
     * Перевірка Міжнародних санкцій / Global Sanctions Lists Screening
     * {
     * "issls": false,
     * "searchedName": "Шевчук Олександр Миколайович",
     * "numberOfMatches": 0,
     * "results": []
     * }
     */
    $resultSanctions = self::_ws(YouControlEnum::sanctions, "v1/individualsGlobalSanctionsLists?$query");
    
    # Помилка отримання даних
    if ('string' === gettype($resultSanctions)) {
      
      return $resultSanctions;
    }
    
    /**
     * Санкції РНБО (RNBO Sanctions)
     * {
     *  "registryUpdateTime": "2023-09-08T13:18:52.385Z",
     *  "result": []
     *  }
     */
    $result = self::_ws(YouControlEnum::sanctions, "v1/individualsRnboSanctions?$query");
    
    $resultSanctions->rnbo = $result;
    
    return $resultSanctions;
  }
  
  /**
   * ФO - Податковий борг (Private individual - Tax debtors)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%91%D0%BE%D1%80%D0%B6%D0%BD%D0%B8%D0%BA%D0%B8%20(Private%20individual%20-%20Debtors)/get_v1_individualsTaxDebtors
   */
  private function _wsTaxDebtor():object | string
  {
    /**
     * {
     * "individualsTaxDebtorsRegistryUpdateTime": "2022-02-23T10:00:00Z",
     * "result": []
     * }
     */
    return self::_ws(YouControlEnum::taxDebtor, "v1/individualsTaxDebtors?".self::_getQuery());
  }
  
  /**
   * Наявність у компанії податкового боргу (Company's tax dept)<br>
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%9F%D0%BE%D0%B4%D0%B0%D1%82%D0%BA%D0%BE%D0%B2%D1%96%20%D0%B4%D0%B0%D0%BD%D1%96%20(Tax%20data)/get_v1_taxDebt__contractorCode_
   */
  private function _wsTaxDebtorCompany():object | string
  {
    /**
     * HTTP 404 - заборгованість відсутня
     * {
     * "code": "NotFound",
     * "message": "No tax debt info for contractor '43745739' found"
     * }
     * HTTP 200 - наявна заборгованість
     * {
     * "debt": 40972.08,
     * "actualDate": "2021-05-01T00:00:00+03:00"
     * }
     */
    $result = self::_ws(YouControlEnum::taxDebtor, "v1/taxDebt/$this->code?");
    
    return 404 === Curl::$curlHttpCode && 'string' === gettype($result) ? new \stdClass() : $result;
  }
  
  /**
   * ФО - Терористи (Terrorists)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%A2%D0%B5%D1%80%D0%BE%D1%80%D0%B8%D1%81%D1%82%D0%B8%20(Terrorists)/get_v1_individualsDsfmuTerrorists
   */
  private function _wsTerrorists():object | string
  {
    /**
     * {
     * "registryUpdateTime": "2022-02-23T10:00:00Z",
     * "result": []
     * }
     */
    return self::_ws(YouControlEnum::terrorists, "v1/individualsDsfmuTerrorists?".self::_getQuery());
  }
  
  /**
   * Безвісно зниклі та ті, які переховуються від органів влади (Missing or wanted persons)
   *
   * @return object|string
   * @link https://api.youscore.com.ua/swagger/index.html#/%D0%A4%D1%96%D0%B7%D0%B8%D1%87%D0%BD%D1%96%20%D0%BE%D1%81%D0%BE%D0%B1%D0%B8%20-%20%D0%91%D0%B5%D0%B7%D0%B2%D1%96%D1%81%D0%BD%D0%BE%20%D0%B7%D0%BD%D0%B8%D0%BA%D0%BB%D1%96%20%D1%82%D0%B0%20%D1%82%D1%96%2C%20%D1%8F%D0%BA%D1%96%20%D0%BF%D0%B5%D1%80%D0%B5%D1%85%D0%BE%D0%B2%D1%83%D1%8E%D1%82%D1%8C%D1%81%D1%8F%20%D0%B2%D1%96%D0%B4%20%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D1%96%D0%B2%20%D0%B2%D0%BB%D0%B0%D0%B4%D0%B8%20(Missing%20or%20wanted%20persons)/get_v1_wantedOrDisappearedPersons
   */
  private function _wsWanted():object | string
  {
    # Строка з даними контрагента
    $query = self::_getQuery();
    $query .= '' === $this->birthday ? '' : "&BirthDate=".urlencode($this->birthday);
    
    /**
     * {
     * "disappearedPersonsRegistryUpdateTime": "2019-10-14T23:32:00Z",
     * "wantedPersonsRegistryUpdateTime": "2019-10-14T23:34:00Z",
     * "disappearedPersons": [],
     * "wantedPersons": []
     * }
     */
    return self::_ws(YouControlEnum::wanted, "v1/wantedOrDisappearedPersons?$query");
    
    /**
     * {
     * "disappearedPersonsRegistryUpdateTime": "2019-10-14T23:32:00Z",
     * "wantedPersonsRegistryUpdateTime": "2019-10-14T23:34:00Z",
     * "disappearedPersons": [],
     * "wantedPersons": []
     * }
     */
    /*
    $result = self::_ws(YouControlEnum::wanted, "v1/wantedOrDisappearedPersons?$query");
    
    if ('string' === gettype($result) || 200 !== Curl::$curlHttpCode
      || empty($result->disappearedPersons)
      && empty($result->wantedPersons)) {
      
      return $result;
    }
    
    # Пошук фото в реєстрі осіб, зниклих безвісти
    foreach ($result->disappearedPersons as $person) {
      foreach ($person->photos as $photo) {
        usleep(300);
        self::_getPhoto($photo);
      }
    }
    
    # Пошук фото в реєстрі осіб, які переховуються від органів влади
    foreach ($result->wantedPersons as $person) {
      foreach ($person->photos as $photo) {
        usleep(300);
        self::_getPhoto($photo);
      }
    }
    
    return $result;*/
  }
}
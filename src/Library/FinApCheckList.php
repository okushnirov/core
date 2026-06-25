<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\HTTPMethods;

final class FinApCheckList
{
  public bool $isDisabled = true;
  
  /**
   * Індивідуальний податковий номер фізичної особи - резидента
   * (більшість реєстрів не містять даних про РНОКПП),
   * або ЄДРПОУ юридичної особи резидента
   *
   * @var string
   */
  private string $code = '';
  
  /**
   * Дата народження фізичної особи у форматі “YYYY-MM-DD”
   *
   * @var string
   */
  private string $date = '';
  
  /**
   * Реквізити документа фізичної особи
   * (національний паспорт, ID картка або паспорт для виїзду за кордон)
   * у форматі серія і номер, або просто номер (якщо ID карта).
   * Наявність або відсутність пробілу між серією і номером значення не має
   *
   * @var string
   */
  private string $doc = '';
  
  /**
   * Тип документа
   * Може приймати значення
   * 1 - ПАСПОРТ ГРОМАДЯНИНА УКРАЇНИ в тому числі й ID карти
   * 2 - ПАСПОРТ ГРОМАДЯНИНА УКРАЇНИ ДЛЯ ВИЇЗДУ ЗА КОРДОН
   * 0 – обидва варіанти (за замовчуванням)
   *
   * @var int
   */
  private int $docType = 0;
  
  private bool $isDebug = false;
  
  private bool $isLoaded = false;
  
  /**
   * Число, кожен біт якого при переведенні у двійковий формат визначає перелік баз (списків)
   * у яких має здійснюватися пошук (або сума чисел, які відповідають пошуковим спискам)
   * 0 1 - ДСФМУ
   * 1 2 - РНБО
   * 2 4 - ООН
   * 3 8 - Європейський союз
   * 4 16 - Велика Британія
   * 5 32 - США
   * 6 64 - Канада
   * 7 128 - Франція, Австралія, Нова Зеландія, Японія, Швейцарія
   * 8 256 - Нотаріуси
   * 9 512 - Національні публічні особи
   * 10 1024 - Публічні особи міжнародних організацій
   * 11 2048 - Іноземні публічні особи
   * 12 4096 - Близькі та пов'язані до публічних осіб
   * 13 8192 - Зрадники та окупанти
   * 14 16384 - Нерезиденти
   * 15 32768 - Реєстр втрачених/недійсних документів (національні та закордонні паспорти)
   * 16 65536 - Реєстр виконавчих проваджень
   * 17 131072 - Реєстр податкових боржників
   * 18 0 - резерв
   * 19 524288 - Реєстр банкрутів
   * 20 1048576 - ЄДР. Причетність до юридичної особи
   * 21 0 - резерв
   * 22 4194304 - Інформація з розширеного ЄДР по юридичній особі
   * 23 8388608 - ЄДР зміни (ОПФ/назва)
   * 24 16777216 - ЄДР зміни (адреса)
   * 25 33554432 - ЄДР зміни (керівник)
   * 26 67108864 - ЄДР зміни (КВЕД)
   * 27 134217728 - ЄДР зміни (статус)
   * 28 268435456 - ЄДР зміни (КБВ)
   * 29 536870912 - ЄДР зміни (власник)
   * 30 1073741824 - Реєстр судових справ
   * 31 0 - Реєстр ліцензій (будівельні та випуск цінних паперів)
   * 32 0 - резерв
   * 33 8589934592 - Реєстр розшуку
   * 34 0 - резерв
   * 35 34359738368 - Реєстр корупціонерів
   *
   * @var int
   */
  private int $listData = 0;
  
  /**
   * Прізвище, ім’я, по батькові або назва особи
   *
   * @var string
   */
  private string $name = '';
  
  /**
   * Внутрішній унікальний ID запиту (генерується на стороні суб’єкта ФМ)
   *
   * @var string
   */
  private string $refID = '';
  
  /**
   * Метод пошуку збігів по реєстрах
   * 1 — повнотекстний (за замовчуванням)
   * 2 — з корекцією помилок
   *
   * @var int
   */
  private int $search = 1;
  
  private string $url = '';
  
  /**
   * ID суб’єкта ФМ (присвоюється при реєстрації суб’єкта ФМ в ПК “FinAP CheckLists”)
   * Приходить на електронну пошту відповідальної особи
   *
   * @var string
   */
  private string $userName = '';
  
  /**
   * ID робочого місця суб’єкта ФМ (присвоюється на стороні суб’єкта ФМ)
   *
   * @var int
   */
  private int $userPCID = 1;
  
  /**
   * Унікальний ключ користувача суб’єкта ФМ (надається при реєстрації суб’єкта ФМ в ПК “FinAP CheckLists”)
   * Приходить на електронну пошту відповідальної особи
   *
   * @var string
   */
  private string $userToken = '';
  
  public function init(object $request, bool $isDebug = false):void
  {
    $this->loadSettings();
    
    $this->isDebug = $isDebug || $this->isDebug;
    
    if ($this->isDebug) {
      trigger_error(__METHOD__."\n".json_encode($request,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), E_USER_ERROR);
    }
    
    $this->refID = isset($request->refID) ? trim((string)$request->refID) : (new \DateTime())->format('ymdHisv');
    $this->code = trim((string)($request->code ?? ''));
    $this->date = trim((string)($request->date ?? ''));
    $this->doc = trim((string)($request->document ?? ''));
    $this->docType = (int)($request->docType ?? $this->docType);
    $this->listData = (int)($request->listData ?? $this->listData);
    $this->name = trim((string)($request->name ?? ''));
    $this->search = (int)($request->search ?? $this->search);
    $this->userPCID = (int)($request->userPCID ?? $this->userPCID);
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
    
    $request = $this->getRequest();
    
    $queryString = http_build_query($request);
    
    $finalUrl = str_contains($this->url, '?') ? $this->url.'&'.$queryString : $this->url.'?'.$queryString;
    
    $response = trim(Curl::exec($finalUrl, httpMethod: HTTPMethods::GET, isDebug: $this->isDebug));
    
    if ($this->isDebug) {
      trigger_error(__METHOD__." Request\n".json_encode($request,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\nResponse\n".$response, E_USER_ERROR);
    }
    
    return $response;
  }
  
  private function getRequest():array
  {
    $request = [
      "IDsubjectFM" => $this->userName,
      "tokken" => $this->userToken,
      "ipn" => $this->code,
      "name" => $this->name,
      "listdata" => $this->listData,
      "search" => $this->search,
      "IDinternal" => $this->refID,
      "IDuserPC" => $this->userPCID
    ];
    
    if ('' !== $this->doc) {
      $request["doc"] = $this->doc;
      $request["docType"] = $this->docType;
    }
    
    if ($this->date) {
      $request["date"] = $this->date;
    }
    
    return $request;
  }
  
  private function loadSettings():void
  {
    Config::load(['fin-ap-checklist.json']);
    
    if (!Config::isLoaded()) {
      
      throw new \Exception('Error reading configuration file', -10);
    }
    
    $this->isDebug = (bool)(Config::get('debug') ?? $this->isDebug);
    $this->isDisabled = (bool)(Config::get('disabled') ?? $this->isDisabled);
    $this->isLoaded = true;
    $this->listData = (int)(Config::get('listData') ?? $this->listData);
    $this->userToken = trim((string)(Config::get('pass') ?? $this->userToken));
    $this->url = trim((string)(Config::get('url') ?? $this->url));
    $this->userName = trim((string)(Config::get('user') ?? $this->userName));
    $this->userPCID = (int)(Config::get('userPCID') ?? $this->userPCID);
  }
}
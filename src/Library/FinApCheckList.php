<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\HTTPMethods;

final class FinApCheckList
{
  public string $code = '';
  
  public string $date = '';
  
  public bool $debug = false;
  
  public bool $disabled = true;
  
  /**
   * Ознака підтвердження результатів запиту підрозділом фінансового моніторингу суб'єкта ФМ:
   * true – потребує підтвердження
   * false – за замовчуванням, без підтвердження
   *
   * @var bool
   */
  public bool $finMon = false;
  
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
   * 23 0 - ЄДР зміни (ОПФ/назва)
   * 24 0 - ЄДР зміни (адреса)
   * 25 0 - ЄДР зміни (керівник)
   * 26 0 - ЄДР зміни (КВЕД)
   * 27 0 - ЄДР зміни (статус)
   * 28 0 - ЄДР зміни (КБВ)
   * 29 0 - ЄДР зміни (власник)
   * 30 1073741824 - Реєстр судових справ
   * 31 0 - Реєстр ліцензій (будівельні та випуск цінних паперів)
   * 32 0 - резерв
   * 33 8589934592 - Реєстр розшуку
   * 34 0 - резерв
   * 35 34359738368 - Реєстр корупціонерів
   *
   * @var int
   */
  public int $listData = 0;
  
  public string $name = '';
  
  public string $refID = '';
  
  /**
   * Тип відповіді (результату запиту):
   * 1 — відповідь-дозвіл (allow)
   * 2 – узагальнений (resume)
   * 3 — скорочений (short)
   * 5 — повний (full, за замовчуванням)
   *
   * @var int
   */
  public int $responseType = 5;
  
  /**
   * Метод пошуку:
   * 1 — повнотекстний (за замовчуванням)
   * 2 — контекстной...
   *
   * @var int
   */
  public int $search = 1;
  
  public string $type = '';
  
  /**
   * ID робочого місця суб'єкта ФМ
   * (присвоюється при реєстрації робочих місць суб'єкта ФМ в ПК "FinAP CheckLists")
   *
   * @var int
   */
  public int $userPCID = 1;
  
  private string $pass = '';
  
  private string $url = '';
  
  private string $user = '';
  
  public function __construct(bool $debug = false)
  {
    $settings = File::parse(['/json/fin-ap-checklist.json']);
    
    $this->debug = $debug || ($settings->debug ?? $this->debug);
    $this->disabled = $settings->disabled ?? $this->disabled;
    $this->listData = $settings->listData ?? $this->listData;
    $this->pass = $settings->pass ?? $this->pass;
    $this->url = $settings->url ?? $this->url;
    $this->user = $settings->user ?? $this->user;
    $this->userPCID = $settings->userPCID ?? $this->userPCID;
  }
  
  public function init(\stdClass $request):void
  {
    if ($this->debug) {
      trigger_error(__METHOD__."\n".json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    $this->refID = $request->refID ?? (new \DateTime())->format('ymdHisu');
    $this->code = trim($request->code ?? '');
    $this->date = trim($request->date ?? '');
    $this->name = trim($request->name ?? '');
    $this->type = trim($request->type ?? '');
    $this->userPCID = (int)($request->userPCID ?? $this->userPCID);
  }
  
  public function sendRequest():string
  {
    if ($this->disabled) {
      
      return '';
    }
    
    $request = self::_getRequest();
    
    if ($this->debug) {
      trigger_error(__METHOD__." Request\n".json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    $response = trim($request ? Curl::exec($this->url, [], $request, httpMethod: HTTPMethods::GET) : '');
    
    if ($this->debug) {
      trigger_error(__METHOD__." Response\n".$response);
    }
    
    return $response;
  }
  
  private function _getRequest():array
  {
    $request = [
      "IDinternal" => $this->refID,
      "DateRequest" => date('Y-m-d'),
      "IDsubjectFM" => $this->user,
      "tokken" => $this->pass,
      "IDuserPC" => $this->userPCID,
      "name" => $this->name,
      "ipn" => $this->code,
      "listdata" => $this->listData,
      "search" => $this->search,
      "finmon" => $this->finMon,
      "responsetype" => $this->responseType
    ];
    
    if ($this->date) {
      $request["date"] = $this->date;
    }
    
    return $request;
  }
}
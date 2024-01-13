<?php

namespace okushnirov\core\Library;

final class FinApCheckList
{
  /**
   * Індивідуальний податковий номер фізичної особи
   * або ЄДРПОУ юридичної особи
   * (необов'язкове поле, більшість списків не містять даних про РНОКПП або ЄДРПОУ)
   *
   * @var string
   */
  public static string $code = '';
  
  /**
   * Дата народження клієнта/контрагента — фізичної особи
   * Дата реєстрації юридичної особи
   * Формат "YYYY-MM-DD"
   *
   * @var string
   */
  public static string $date = '';
  
  /**
   * Режим налагодження
   *
   * @var bool
   */
  public static bool $debug = false;
  
  /**
   * Сервіс вимкнено
   */
  public static bool $disabled = true;
  
  /**
   * Ознака підтвердження результатів запиту підрозділом фінансового моніторингу суб'єкта ФМ:
   * true – потребує підтвердження
   * false – по замовчуванню, без підтвердження
   *
   * @var bool
   */
  public static bool $finMon = false;
  
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
   * 15 32768 - Втрачені/загублені національні та закордонні паспорта
   * 16 65536 - Реєстр виконавчих проваджень
   * 17 131072 - Податкові боржники
   * 18 0 - резерв
   * 19 524288 - Банкрути
   * 20 1048576 - ЄДР. Причетність до юридичної особи
   * 21 2097152 - Інформація з ЄДР по юридичній особі
   * 22 4194304 - Інформація з розширеного ЄДР по юридичній особі
   * 23 0 - ЄДР зміни (ОПФ/назва)
   * 24 0 - ЄДР зміни (адреса)
   * 25 0 - ЄДР зміни (керівник)
   * 26 0 - ЄДР зміни (КВЕД)
   * 27 0 - ЄДР зміни (статус)
   * 28 0 - ЄДР зміни (КБВ)
   * 29 0 - ЄДР зміни (власник)
   * 30 1073741824 - Судові справи
   * 31 0 - Ліцензії (будівельні та випуск цінних паперів)
   * 32 0 - резерв
   * 33 8589934592 - Розшук
   * 34 0 - резерв
   * 35 34359738368 - Корупціонери
   *
   * @var int
   */
  public static int $listData = 0;
  
  /**
   * Прізвище, ім'я, по батькові або назва клієнта/контрагента
   *
   * @var string
   */
  public static string $name = '';
  
  /**
   * Внутрішній унікальний ID запиту
   * (генерується засобами внутрішнього програмного забезпечення суб'єкта ФМ)
   *
   * @var string
   */
  public static string $refID = '';
  
  /**
   * Тип відповіді (результату запиту):
   * 1 — відповідь-дозвіл (allow)
   * 2 – узагальнений (resume)
   * 3 — скорочений (short)
   * 5 — повний (full, по замовчуванню)
   *
   * @var int
   */
  public static int $responseType = 5;
  
  /**
   * Метод пошуку:
   * 1 — повнотекстний (за замовчуванням)
   * 2 — контекстной...
   *
   * @var int
   */
  public static int $search = 1;
  
  /**
   * Тип клієнта
   *
   * @var string
   */
  public static string $type = '';
  
  /**
   * ID робочого місця суб'єкта ФМ
   * (присвоюється при реєстрації робочих місць суб'єкта ФМ в ПК "FinAP CheckLists")
   *
   * @var int
   */
  public static int $userPCID = 2;
  
  /**
   * Пароль
   *
   * @var string
   */
  private static string $_pass = "";
  
  /**
   * URL
   *
   * @var string
   */
  private static string $_url = "https://finap.com.ua:9443/api";
  
  /**
   * Логін
   *
   * @var string
   */
  private static string $_user = "";
  
  public function __construct()
  {
    $settings = File::parse(['/json/fin-ap-checklist.json']);
    
    self::$disabled = $settings->disabled ?? self::$disabled;
    self::$listData = $settings->listData ?? self::$listData;
    self::$_pass = $settings->pass ?? self::$_pass;
    self::$_user = $settings->user ?? self::$_user;
  }
  
  /**
   * Підготовка запиту
   *
   * @return array
   */
  private static function _getRequest():array
  {
    # Запрос
    $request = [
      "IDinternal" => self::$refID,
      "DateRequest" => date('Y-m-d'),
      "IDsubjectFM" => self::$_user,
      "tokken" => self::$_pass,
      "IDuserPC" => self::$userPCID,
      "name" => self::$name,
      "ipn" => self::$code,
      "listdata" => self::$listData,
      "search" => self::$search,
      "finmon" => self::$finMon,
      "responsetype" => self::$responseType
    ];
    
    # Дата народження/реєстрації за наявності
    if (self::$date) {
      $request["date"] = self::$date;
    }
    
    return $request;
  }
  
  /**
   * Ініціалізація
   *
   * @param \stdClass $data
   */
  public static function init(\stdClass $data):void
  {
    if (self::$debug) {
      trigger_error(__METHOD__."\n".json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    self::$refID = $data->refID ?? (new \DateTime())->format('ymdHisu');
    self::$code = trim($data->code ?? '');
    self::$date = trim($data->date ?? '');
    self::$name = trim($data->name ?? '');
    self::$type = trim($data->type ?? '');
    self::$userPCID = (int)($data->userPCID ?? self::$userPCID);
  }
  
  /**
   * Запит до API
   *
   * @return string
   */
  public static function sendRequest():string
  {
    if (self::$disabled) {
      
      return '';
    }
    
    $request = self::_getRequest();
    
    if (self::$debug) {
      trigger_error(__METHOD__." Request\n".json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    $response = $request ? (string)Curl::exec(self::$_url, [], $request, '', '', 0) : '';
    
    if (self::$debug) {
      trigger_error(__METHOD__." Response\n".$response);
    }
    
    return $response;
  }
}
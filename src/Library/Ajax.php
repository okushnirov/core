<?php

namespace okushnirov\core\Library;

use JetBrains\PhpStorm\NoReturn;
use okushnirov\core\Auth\{Authorization, UserSession};
use okushnirov\core\Handlers\{ErrorRequest, TraceHandler};
use okushnirov\core\Library\Enums\{Auth, CookieType, SessionType};

final class Ajax
{
  public static string $case = '';
  
  public static false | string $contents = false;
  
  public static ?array $in = [];
  
  public static AjaxOut $out;
  
  public static mixed $settings;
  
  public function __construct(
    array $jsonConfigs = [], SessionType $session = SessionType::WS, CookieType $cookie = CookieType::Yes)
  {
    self::boot($jsonConfigs, $session, $cookie);
  }
  
  public static function init(bool $isOnlyLocal = true, bool $traceError = true):bool
  {
    if ($isOnlyLocal && !self::isLocalRequest()) {
      self::handleForbiddenRequest($traceError);
      
      exit;
    }
    
    self::$case = $_POST['case'] ?? '';
    self::$out->success = !empty(self::$case);
    
    if (self::$out->success) {
      unset($_POST['case']);
      
      self::$contents = file_get_contents('php://input');
      self::$in = $_POST ?? [];
    }
    
    return self::$out->success;
  }
  
  public static function isLogin(Auth $type = Auth::DB_USER, int | bool $connection = false):bool
  {
    $auth = new Authorization(new UserSession());
    
    $isAuthorized = $auth->isActiveUserSession() || $auth->check($type, $connection);
    
    self::$out->reload = !$isAuthorized;
    self::$out->success = $isAuthorized;
    
    return self::$out->success;
  }
  
  #[NoReturn]
  public static function result(int $errorCode = -3):void
  {
    if (!self::$out->success && !self::$out->reload) {
      self::$out->errorCode ??= $errorCode;
      
      # Суворе порівняння з порожнім рядком дозволяє розрізнити:
      # '' (дефолт) -> підставляємо стандартну помилку
      # null (заглушено в коді) -> ігноруємо автопідставлення, виводимо як є
      if ('' === self::$out->errorMessage) {
        self::$out->errorMessage = self::$settings->error->{self::$out->errorCode}->{Lang::$lang} ??
          self::$settings->error->{self::$out->errorCode} ?? "Undefined error [".self::$out->errorCode."]";
      }
    }
    
    exit(json_encode(self::$out));
  }
  
  private static function boot(array $jsonConfigs, SessionType $session, CookieType $cookie):void
  {
    if (SessionType::NONE !== $session) {
      Session::sessionStart($session);
    }
    
    Lang::set($session, $cookie);
    
    $folder = SessionType::NONE === $session ? (Location::$folder ?? '') : ($_SESSION['folder'] ?? '');
    
    Location::$folder = $folder;
    
    self::$out = new AjaxOut();
    
    $suffix = match ($folder) {
      '', '/' => '',
      default => '-'.$folder
    };
    
    $fileRoot = "root$suffix.php";
    $jsonRoot = File::isFile("/php/classes/Configs/$fileRoot") ? [$fileRoot] : [];
    
    Config::load(array_merge([
      'dbase.php',
      'error.php',
      'message.php'
    ], $jsonRoot, $jsonConfigs));
    
    self::$settings = Config::getAsObject();
  }
  
  private static function handleForbiddenRequest(bool $traceError):void
  {
    if ($traceError) {
      (new TraceHandler(403, [
        'DEBUG' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
        'COOKIE' => $_COOKIE ?? [],
        'REQUEST' => $_REQUEST ?? [],
        'SESSION' => $_SESSION ?? []
      ]))->log();
    } else {
      (new ErrorRequest('/error/', 403))->run();
    }
  }
  
  private static function isLocalRequest():bool
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      
      return false;
    }
    
    $headers = array_change_key_case(getallheaders());
    
    # 1. Перевірка сучасних браузерних заголовків
    if (isset($headers['sec-fetch-site'])) {
      
      return $headers['sec-fetch-site'] === 'same-origin';
    }
    
    # 2. Фол бек на HTTP_ORIGIN + Перевірка CSRF - токену (для повної безпеки)
    $clientToken = $headers['x-csrf-token'] ?? $_POST['csrf_token'] ?? '';
    $serverToken = $_SESSION['csrf_token'] ?? '';
    
    $isTokenValid = !empty($serverToken) && hash_equals($serverToken, $clientToken);
    
    return isset($_SERVER['HTTP_ORIGIN'])
      && str_starts_with(Location::serverName(), $_SERVER['HTTP_ORIGIN'])
      && $isTokenValid;
  }
}
<?php

namespace okushnirov\core\Library;

use okushnirov\core\{Handlers\ErrorRequest, Library\Enums\Auth, Library\Enums\CookieType, Library\Enums\SessionType};
use JetBrains\PhpStorm\NoReturn;

final class Ajax
{
  public static string $case = '';
  
  public static false | string $contents = false;
  
  public static ?array $in = [];
  
  public static AjaxOut $out;
  
  public static mixed $settings;
  
  public function __construct(
    array $JSON = [], SessionType $session = SessionType::WS, CookieType $cookie = CookieType::Yes)
  {
    if (SessionType::NONE !== $session) {
      Session::sessionStart($session);
    }
    
    Lang::set($session, $cookie);
    Location::$folder = SessionType::NONE === $session ? (Location::$folder ?? '') : ($_SESSION['folder'] ?? '');
    
    self::$out = new AjaxOut();
    
    $filename = '/json/root'.match (Location::$folder) {
        '', '/' => '',
        default => '-'.Location::$folder
      }.'.json';
    
    $JSONRoot = $filename && File::isFile($filename) ? [$filename] : [];
    
    self::$settings = File::parse(array_merge([
      '/json/dbase.json',
      '/json/error.json',
      '/json/message.json'
    ], $JSONRoot, is_array($JSON) ? $JSON : []));
  }
  
  public static function init(bool $onlyLocal = true):bool
  {
    # If not local requests
    if (!TEST_SERVER && $onlyLocal
      && ('POST' !== $_SERVER['REQUEST_METHOD'] || !isset($_SERVER['HTTP_ORIGIN'])
        || !str_starts_with(Location::serverName(), $_SERVER['HTTP_ORIGIN']))) {
      (new ErrorRequest('/error/', 403, [
        'DEBUG' => debug_backtrace(),
        'COOKIE' => $_COOKIE ?? [],
        'REQUEST' => $_REQUEST ?? [],
        'SESSION' => Session::decryptCRC($_SESSION ?? [])
      ]))::run();
      
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
    $auth = new Authorization();
    
    self::$out->success = !self::$out->reload = !($auth->updateSession() || $auth->check($type, $connection));
    
    return self::$out->success;
  }
  
  #[NoReturn] public static function result(int $errorCode = -3):void
  {
    if (!self::$out->success && !self::$out->reload) {
      self::$out->errorCode = null === self::$out->errorCode ? $errorCode : self::$out->errorCode;
      self::$out->errorMessage = '' !== self::$out->errorMessage
        ? self::$out->errorMessage
        : (self::$settings->error->{self::$out->errorCode}->{Lang::$lang} ??
          self::$settings->error->{self::$out->errorCode} ?? 'Undefined error ['.self::$out->errorCode.']');
    }
    
    exit(json_encode(self::$out));
  }
}
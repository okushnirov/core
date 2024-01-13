<?php

namespace okushnirov\core\Handlers;

require_once __DIR__.'/../../php/classes/Constant.php';

class ErrorHandler
{
  static array $errorType = [
    E_ERROR => 'Помилка',
    E_WARNING => 'Попередження',
    E_PARSE => 'Помилка аналізу вихідного коду',
    E_NOTICE => 'Повідомлення',
    E_CORE_ERROR => 'Помилка ядра',
    E_CORE_WARNING => 'Попередження ядра',
    E_COMPILE_ERROR => 'Помилка на етапі компіляції',
    E_COMPILE_WARNING => 'Попередження на етапі компіляції',
    E_USER_ERROR => 'Угода користувача',
    E_USER_WARNING => 'Попередження користувача',
    E_USER_NOTICE => 'Повідомлення користувача',
    E_STRICT => 'Повідомлення часу виконання',
    E_RECOVERABLE_ERROR => 'Фатальна помилка, що перехоплюється',
    E_DEPRECATED => 'Використання застарілих конструкцій',
    E_USER_DEPRECATED => 'Використання застарілих конструкцій'
  ];
  
  public function errorHandler(
    int $errorNumber, string $errorMessage, string $errorFileName, int $errorLineNumber):bool
  {
    if (!(error_reporting() & $errorNumber)) {
      
      return false;
    }
    
    try {
      $date = new \DateTime();
      $date = $date->format("Y-m-d H:i:s.u");
    } catch (\Exception) {
      $time = microtime(true);
      $date = date('Y-m-d H:i:s.').sprintf("%06d", ($time - floor($time)) * 1000000);
    }
    
    if (E_USER_ERROR === $errorNumber) {
      $errorString = "\n$errorMessage";
    } else {
      $errorString = "\n$date";
      $errorString .= " [{$_SERVER['REMOTE_ADDR']}] ";
      $errorString .= self::$errorType[$errorNumber] ?? "[$errorNumber]";
      $errorString .= !mb_stripos($errorMessage, ' on line ') ? " -> $errorFileName on line $errorLineNumber" : '';
      $errorString .= "\n$errorMessage\n";
    }
    
    return error_log($errorString, 3, "C:\\Log\\{$_SERVER['SERVER_NAME']}.log");
  }
  
  public function exceptionHandler(\Throwable $e):bool
  {
    
    return self::errorHandler(E_ERROR, get_class($e).' '.$e->getMessage().' '.$e->getCode(), $e->getFile(),
      $e->getLine());
  }
  
  public function register():void
  {
    error_reporting(E_ALL);
    
    set_error_handler([
      $this,
      'errorHandler'
    ]);
    
    register_shutdown_function([
      $this,
      'shutdownHandler'
    ]);
    
    set_exception_handler([
      $this,
      'exceptionHandler'
    ]);
  }
  
  public function shutdownHandler():void
  {
    if ($error = @error_get_last()) {
      $errorNumber = (int)($error->type ?? 0);
      
      if (0 < $errorNumber) {
        self::errorHandler($errorNumber, $error->message ?? '', $error->file ?? '', (int)($error->line ?? 0));
      }
    }
  }
}
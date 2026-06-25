<?php

namespace okushnirov\core\Handlers;

final class ErrorHandler
{
  private array $errorType = [
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
  
  private string $logPath = "C:\\Log\\";
  
  public function __construct(string $logPath = '', string $constantsFile = '')
  {
    $this->logPath = '' === $logPath ? $this->logPath : $logPath;
    
    if ('' !== $constantsFile && file_exists($constantsFile)) {
      require_once $constantsFile;
    }
  }
  
  public function errorHandler(
    int $errorNumber, string $errorMessage, string $errorFileName, int $errorLineNumber):bool
  {
    if (!(error_reporting() & $errorNumber)) {
      
      return false;
    }
    
    if (E_USER_ERROR === $errorNumber) {
      $errorString = "\n$errorMessage";
    } else {
      $errorString = "\n".date('Y-m-d H:i:s.').substr(explode(' ', microtime())[0], 2, 6);
      $errorString .= ' ['.($_SERVER['REMOTE_ADDR'] ?? 'localhost (CLI)').'] ';
      $errorString .= $this->errorType[$errorNumber] ?? "[$errorNumber]";
      $errorString .= false === mb_stripos($errorMessage, ' on line ') ? " -> $errorFileName on line $errorLineNumber"
        : '';
      $errorString .= "\n$errorMessage\n";
    }
    
    $logFile = ($_SERVER['SERVER_NAME'] ?? 'cli').'.log';
    
    $finalPath = is_dir($this->logPath)
      ? $this->logPath.$logFile
      : rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$logFile;
    
    restore_error_handler();
    
    $result = error_log($errorString, 3, $finalPath);
    
    set_error_handler([
      $this,
      'errorHandler'
    ]);
    
    return $result;
  }
  
  public function exceptionHandler(\Throwable $e):bool
  {
    
    return $this->errorHandler(E_ERROR, get_class($e).' '.$e->getMessage().' '.$e->getCode(), $e->getFile(),
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
      $errorNumber = (int)($error['type'] ?? 0);
      
      if (0 < $errorNumber) {
        $this->errorHandler($errorNumber, $error['message'] ?? '', $error['file'] ?? '', (int)($error['line'] ?? 0));
      }
    }
  }
}
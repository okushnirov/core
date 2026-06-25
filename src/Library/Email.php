<?php

namespace okushnirov\core\Library;

use PHPMailer\PHPMailer\{Exception, PHPMailer, SMTP};

final class Email
{
  private static ?string $host = null;
  private static bool $isAuth = false;
  private static bool $isDebug = false;
  private static string $pass = '';
  private static ?int $port = 25;
  private static string $secure = '';
  private static array $sender = [];
  private static int $timeout = 5;
  private static string $user = '';
  
  public function __construct(string $eName = '', array $config = [], bool $isDebug = false)
  {
    if ('' === $eName && empty($config['host'])) {
      
      throw new \Exception("Empty hostname", -1);
    }
    
    Config::load(['email.php']);
    
    $emailConfig = '' === $eName ? null : (Config::get($eName) ?? null);
    
    if ('' !== $eName && empty($emailConfig)) {
      
      throw new \Exception("Email settings not found", -2);
    }
    
    self::$host = $config['host'] ?? $emailConfig['host'] ?? self::$host;
    self::$sender = (array)($config['sender'] ?? $emailConfig['sender'] ?? self::$sender);
    
    if (empty(self::$host) || empty(self::$sender)) {
      
      throw new \Exception("Wrong required parameters", -3);
    }
    
    self::$isAuth = (bool)($config['auth'] ?? $emailConfig['auth'] ?? self::$isAuth);
    self::$isDebug = (bool)($config['debug'] ?? $isDebug);
    self::$pass = trim(($config['pass'] ?? $emailConfig['pass'] ?? self::$pass));
    self::$port = (int)($config['port'] ?? $emailConfig['port'] ?? self::$port);
    self::$secure = upper_case(trim(($config['secure'] ?? $emailConfig['secure'] ?? self::$secure)));
    self::$user = trim(($config['user'] ?? $emailConfig['user'] ?? self::$user));
  }
  
  public static function send(array $addresses, string $subject, string $message, array $attachments = []):bool
  {
    $mail = new PHPMailer(true);
    $result = false;
    
    try {
      $mail->SMTPDebug = self::$isDebug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
      $mail->isSMTP();
      $mail->CharSet = $mail::CHARSET_UTF8;
      $mail->Host = self::$host;
      
      if (self::$isAuth) {
        $mail->SMTPAuth = true;
        $mail->Username = self::$user;
        $mail->Password = self::$pass;
      }
      
      $mail->SMTPSecure = match (self::$secure) {
        'TLS' => PHPMailer::ENCRYPTION_STARTTLS,
        'SSL' => PHPMailer::ENCRYPTION_SMTPS,
        default => ''
      };
      
      $mail->Port = self::$port;
      $mail->Timeout = self::$timeout;
      
      $mail->setFrom(self::$sender[0], self::$sender[1] ?? self::$sender[0]);
      
      foreach ($addresses as $address) {
        if (empty($address)) {
          
          continue;
        }
        
        if (is_array($address)) {
          $mail->addAddress($address[0], trim($address[1] ?? $address[0]));
        } else {
          $mail->addAddress($address, $address);
        }
      }
      
      $mail->isHTML();
      $mail->Subject = $subject;
      $mail->Body = $message;
      
      foreach ($attachments as $attachment) {
        if (empty($attachment)) {
          
          continue;
        }
        
        if (is_array($attachment)) {
          $mail->addAttachment($attachment[0], $attachment[1] ?? '');
        } else {
          $mail->addAttachment($attachment);
        }
      }
      
      $result = $mail->send();
    } catch (Exception $e) {
      trigger_error(__METHOD__." Error message\n".$e->getMessage());
    }
    
    return $result;
  }
}
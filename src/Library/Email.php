<?php

namespace okushnirov\core\Library;

use PHPMailer\PHPMailer\{Exception, PHPMailer, SMTP};

final class Email
{
  protected static bool $auth = false;
  
  protected static bool $debug = false;
  
  protected static string $pass = '';
  
  protected static ?int $port = 25;
  
  protected static array $sender = [];
  
  protected static string $user = '';
  
  private static ?string $host;
  
  private static string $secure = '';
  
  private static int $timeout = 5;
  
  public function __construct(string $eName = '', array $input = [])
  {
    $settings = File::parse(['/json/email.json']);
    
    if ('' === $eName && empty($input)) {
      
      throw new \Exception("Empty hostname", -1);
    }
    
    $email = $settings->{$eName} ?? null;
    
    if (empty($email)) {
      
      throw new \Exception("Email settings not found", -2);
    }
    
    self::$host = (bool)($input['host'] ?? $email->host ?? self::$host);
    self::$sender = (array)($input['sender'] ?? $email->sender ?? self::$sender);
    
    if (empty(self::$host) || empty(self::$sender)) {
      
      throw new \Exception("Wrong required parameters", -3);
    }
    
    self::$auth = (bool)($settings->auth ?? $input['auth'] ?? self::$auth);
    self::$debug = (bool)($input['debug'] ?? self::$debug);
    self::$pass = trim($input['pass'] ?? $email->pass ?? self::$pass);
    self::$port = (int)($input['port'] ?? $email->port ?? self::$port);
    self::$user = trim($input['user'] ?? $email->user ?? self::$user);
  }
  
  public static function send(array $addresses, string $subject, string $message, array $attachments = []):bool
  {
    $mail = new PHPMailer(true);
    $result = false;
    
    try {
      $mail->SMTPDebug = self::$debug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
      $mail->isSMTP();
      $mail->CharSet = $mail::CHARSET_UTF8;
      $mail->Host = self::$host;
      
      if (self::$auth) {
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
      trigger_error(__METHOD__."\n".$e->getMessage());
    }
    
    return $result;
  }
}
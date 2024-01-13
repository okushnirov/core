<?php

namespace okushnirov\core\Library;

class Person
{
  public static string $authority;
  
  public static string $avatar;
  
  public static int $blockCnt;
  
  public static string $branch;
  
  public static ?int $depID;
  
  public static string $depName;
  
  public static string $depRef;
  
  public static ?int $id;
  
  public static bool $isBlock;
  
  public static bool $isHeadOffice;
  
  public static bool $isLocked;
  
  public static bool $isWork;
  
  public static string $key;
  
  public static int $lockedCnt;
  
  public static string $login;
  
  public static string $email;
  
  public static string $name;
  
  public static string $pass;
  
  public static string $phone;
  
  public static string $post;
  
  public static string $role;
  
  public static string $telegram;
  
  public static string $type;
  
  public static function setValue(array $user):void
  {
    self::$authority = $user['authority'] ?? '';
    
    self::$avatar = trim($user['avatar'] ?? '');
    
    self::$blockCnt = (int)($user['blockCnt'] ?? 0);
    
    self::$branch = $user['branch'] ?? '';
    
    $userDepID = trim($user['depID'] ?? '');
    self::$depID = '' === $userDepID ? null : (int)$userDepID;
    
    self::$depName = $user['depName'] ?? '';
    
    self::$depRef = $user['depRef'] ?? '';
    
    self::$email = $user['email'] ?? '';
    
    self::$id = (int)$user['id'];
    
    self::$isBlock = !empty($user['block']);
    
    self::$isHeadOffice = !empty($user['headOffice']);
    
    self::$isLocked = !empty($user['locked']);
    
    self::$isWork = !empty($user['work']);
    
    self::$key = $user['key'] ?? '';
    
    self::$lockedCnt = (int)($user['lockedCnt'] ?? 0);
    
    self::$login = $user['login'];
    
    self::$name = trim($user['name'] ?? '');
    self::$name = '' === self::$name ? self::$login : self::$name;
    
    self::$pass = $user['hash'] ?? '';
    
    self::$phone = $user['phone'] ?? '';
    
    self::$post = $user['post'] ?? '';
    
    self::$role = $user['role'] ?? '';
    
    self::$telegram = $user['telegram'] ?? '';
    
    self::$type = $user['type'] ?? '';
  }
}
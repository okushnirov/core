<?php

namespace okushnirov\core\Library;

class Person
{
  public static string $authority = '';
  public static string $avatar = '';
  public static int $blockCnt = 0;
  public static string $branch = '';
  public static ?int $depID = null;
  public static string $depName = '';
  public static string $depRef = '';
  public static string $email = '';
  public static ?int $id = null;
  public static bool $isBlock = false;
  public static bool $isHeadOffice = false;
  public static bool $isLocked = false;
  public static bool $isWork = false;
  public static string $key = '';
  public static int $lockedCnt = 0;
  public static string $login = '';
  public static string $name = '';
  public static string $pass = '';
  public static string $phone = '';
  public static string $post = '';
  public static string $role = '';
  public static string $telegram = '';
  public static string $type = '';
  
  public static function setValue(array $user):void
  {
    self::$authority = trim((string)($user['authority'] ?? ''));
    self::$avatar = trim((string)($user['avatar'] ?? ''));
    self::$blockCnt = (int)($user['blockCnt'] ?? 0);
    self::$branch = trim((string)($user['branch'] ?? ''));
    $depID = trim((string)($user['depID'] ?? ''));
    self::$depID = '' === $depID ? null : (int)$depID;
    self::$depName = trim((string)($user['depName'] ?? ''));
    self::$depRef = trim((string)($user['depRef'] ?? ''));
    self::$email = trim((string)($user['email'] ?? ''));
    self::$id = isset($user['id']) ? (int)$user['id'] : null;
    self::$isBlock = !empty($user['block']);
    self::$isHeadOffice = !empty($user['headOffice']);
    self::$isLocked = !empty($user['locked']);
    self::$isWork = !empty($user['work']);
    self::$key = trim((string)($user['key'] ?? ''));
    self::$lockedCnt = (int)($user['lockedCnt'] ?? 0);
    self::$login = trim((string)($user['login'] ?? ''));
    self::$name = trim((string)($user['name'] ?? ''));
    self::$name = '' === self::$name ? self::$login : self::$name;
    self::$pass = trim((string)($user['hash'] ?? ''));
    self::$phone = trim((string)($user['phone'] ?? ''));
    self::$post = trim((string)($user['post'] ?? ''));
    self::$role = trim((string)($user['role'] ?? ''));
    self::$telegram = trim((string)($user['telegram'] ?? ''));
    self::$type = trim((string)($user['type'] ?? ''));
  }
}
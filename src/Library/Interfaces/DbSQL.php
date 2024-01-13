<?php

namespace okushnirov\core\Library\Interfaces;

use okushnirov\core\Library\Enums\SQLAnywhere;

interface DbSQL
{
  public static function connect(
    bool | int $connection = false, bool | string $user = false, bool | string $pass = false):bool;
  
  public static function disconnect():void;
  
  public static function escape(
    mixed         $queryString, bool $wrapQuotes = false, bool | int $connection = false, bool | string $user = false,
    bool | string $pass = false):bool | string;
  
  public static function query(
    string        $queryString, SQLAnywhere $type, bool | int $connection = false, bool | string $user = false,
    bool | string $pass = false, string $keyString = '', int $flags = 0):mixed;
}
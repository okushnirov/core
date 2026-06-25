<?php

namespace okushnirov\core\Library;

use core\Handlers\LangHandler;

final class Config
{
  private const CONFIG_BASE_PATH = 'php\classes\Configs\\';
  private static ?LangHandler $lang = null;
  private static ?string $projectRoot = null;
  private static mixed $settings = [];
  
  public static function get(string $key, mixed $default = null):mixed
  {
    if (isset(self::$settings[$key])) {
      
      return self::$settings[$key];
    }
    
    $segments = explode('.', $key);
    $data = self::$settings;
    
    foreach ($segments as $segment) {
      if (!is_array($data) || !array_key_exists($segment, $data)) {
        
        return $default;
      }
      
      $data = $data[$segment];
    }
    
    return $data ?? $default;
  }
  
  public static function getAsArray():?array
  {
    
    return self::$settings;
  }
  
  public static function getAsObject():?\stdClass
  {
    
    return json_decode(json_encode(self::$settings, JSON_FORCE_OBJECT));
  }
  
  public static function getMessage(string $key, ?LangHandler $lang = null, string $default = ''):string
  {
    $node = self::get($key);
    
    if (is_null($node)) {
      
      return $default;
    }
    
    if (!is_array($node)) {
      
      return is_null($lang) ? (string)$node : $default;
    }
    
    $activeLang = $lang ?? self::$lang;
    
    if (is_null($activeLang)) {
      
      return $default;
    }
    
    return $node[$activeLang->value] ?? $default;
  }
  
  public static function isLoaded():bool
  {
    
    return !empty(self::$settings);
  }
  
  public static function load(array $files, LangHandler $lang = LangHandler::UK):void
  {
    self::$lang = $lang;
    
    $merged = [];
    
    $rootDir = dirname((new \ReflectionClass(\Composer\Autoload\ClassLoader::class))->getFileName(), 3);
    
    self::$projectRoot = self::$projectRoot
      ? : $rootDir.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR,
        self::CONFIG_BASE_PATH);
    
    foreach ($files as $file) {
      $fileCleaned = ltrim($file, '\\/');
      
      $filePath = str_starts_with($fileCleaned, './')
        ? $rootDir.ltrim($fileCleaned, '.')
        : self::$projectRoot.$fileCleaned;
      
      if (file_exists($filePath)) {
        if (str_ends_with($filePath, '.php')) {
          $data = require $filePath;
        } else {
          $data = File::parse($filePath, isRoot: false);
        }
        
        if (is_array($data)) {
          $merged = array_replace_recursive($merged, $data);
        }
      }
    }
    
    self::$settings = $merged;
  }
}
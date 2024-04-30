<?php

namespace okushnirov\core\Handlers;

use okushnirov\core\Library\File;

class RootSettings
{
  public static false | string $contents = false;
  
  public static array $get = [];
  
  public static mixed $json;
  
  public static array $path;
  
  public static array $post = [];
  
  public static mixed $root;
  
  public function __construct(array $JSON = [])
  {
    self::$contents = file_get_contents('php://input');
    
    $requestURI = trim($_SERVER['REQUEST_URI'] ?? '');
    
    parse_str(trim((string)parse_url($requestURI, PHP_URL_QUERY)), self::$get);
    
    self::$path = explode('/', (string)parse_url(trim($requestURI, '/'), PHP_URL_PATH));
    
    $reflectionClass = (new \ReflectionClass($this));
    
    # Get folder name from PHPDoc /** * @folder {name} */
    if ($reflectionClass->getDocComment()) {
      preg_match_all("/(?<=@folder ).*/m", $reflectionClass->getDocComment(), $matches);
      $folder = $matches ? str_replace('\r', '', trim($matches[0][0] ?? '')) : '';
    }
    
    $folder = isset($folder) && $folder
      ? $folder
      : mb_eregi_replace(str_replace("\\", '-', Root::ROOT_PATH.Root::ROOT_FOLDERS), '', str_replace('\\', '-',
        mb_strtolower($reflectionClass->getNamespaceName().'-'.$reflectionClass->getShortName())));
    $folder = 'core-root-index' === $folder ? '/' : "$folder";
    
    $file = '/' === $folder || '' === $folder ? '' : "/json/root-$folder.json";
    
    self::$json = File::parse(array_merge(['/json/root.json'], $file && File::isFile($file) ? [$file] : [], $JSON));
    self::$post = $_POST;
    self::$root = self::$json->root->{$folder} ?? false;
  }
}
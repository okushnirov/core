<?php

namespace okushnirov\core\Library;

final class FileIO
{
  public static function getFileExtension(string $file):string
  {
    $path_info = pathinfo($file);
    
    return $path_info['extension'] ?? '';
  }
  
  public static function loadFile(string $filename):string
  {
    if (!empty($filename) && file_exists($filename) && !is_dir($filename)) {
      $content = file_get_contents($filename);
      
      return false === $content ? '' : $content;
    }
    
    return throw new \Exception('File not found', -1);
  }
  
  public static function writeFile(string $filename, string $data = ''):bool
  {
    if ('' === $filename) {
      
      return throw new \Exception('Empty filename', -1);
    }
    
    $objFile = fopen($filename, 'w+');
    
    if ('resource' === gettype($objFile)) {
      fwrite($objFile, $data);
      
      return fclose($objFile);
    }
    
    return true;
  }
}
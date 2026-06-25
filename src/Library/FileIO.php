<?php

namespace okushnirov\core\Library;

final class FileIO
{
  public static function getFileExtension(string $file):string
  {
    $basename = basename($file);
    
    $lastDotPos = mb_strrpos($basename, '.');
    
    if (false === $lastDotPos || $lastDotPos === mb_strlen($basename) - 1) {
      
      return '';
    }
    
    return mb_strtolower(mb_substr($basename, $lastDotPos + 1));
  }
  
  public static function loadFile(string $filename):string
  {
    if ('' !== $filename && file_exists($filename) && !is_dir($filename)) {
      $content = file_get_contents($filename);
      
      return false === $content ? '' : $content;
    }
    
    throw new \Exception('File not found', -1);
  }
  
  public static function writeFile(string $filename, string $data = ''):bool
  {
    if ('' === $filename) {
      
      throw new \Exception('Empty filename', -2);
    }
    
    $objFile = @fopen($filename, 'w+');
    
    if (is_resource($objFile)) {
      fwrite($objFile, $data);
      
      return fclose($objFile);
    }
    
    return false;
  }
}
<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\FileType;

final class File
{
  public static mixed $settings;
  
  public static function isEmptyDir(string $dir):bool
  {
    
    return is_dir($dir) && !(new \FilesystemIterator($dir))->valid();
  }
  
  public static function isFile(string $file, bool $isRoot = true):bool
  {
    $filename = $isRoot ? $_SERVER['DOCUMENT_ROOT'].$file : $file;
    
    return '' !== $file && file_exists($filename) && !is_dir($filename);
  }
  
  public static function parse(
    array | string $input, FileType $fileType = FileType::JSON, bool $objectResult = true, bool $isRoot = true):mixed
  {
    $files = is_array($input) ? $input : ('string' === gettype($input) ? [$input] : '');
    $resultArray = [];
    $resultString = '';
    
    if (empty($files)) {
      
      throw new \Exception('Empty files', -1);
    }
    
    foreach ($files as $file) {
      if (!self::isFile($file, $isRoot)) {
        
        throw new \Exception("File $file not found", -2);
      }
      
      $filePath = $isRoot ? $_SERVER['DOCUMENT_ROOT'].$file : $file;
      
      $fileContent = match ($fileType) {
        FileType::JSON => json_decode(file_get_contents($filePath), true),
        FileType::XML => (array)simplexml_load_file($filePath),
        FileType::INI => parse_ini_file($filePath, true),
        FileType::SERIALIZE => unserialize(file_get_contents($filePath)),
        FileType::ANY => file_get_contents($filePath)
      };
      
      if (false === $fileContent) {
        
        continue;
      }
      
      if ($fileType === FileType::ANY) {
        $resultString .= $fileContent;
      } else {
        $resultArray = array_merge_recursive($resultArray, $fileContent);
      }
      
      unset($fileContent);
    }
    
    return $fileType === FileType::ANY ? $resultString
      : ($objectResult ? json_decode(json_encode($resultArray, JSON_FORCE_OBJECT)) : $resultArray);
  }
}
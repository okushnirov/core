<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\FileType;

final class File
{
  public static function isEmptyDir(string $dir):bool
  {
    if (!is_dir($dir)) {
      
      return false;
    }
    
    $iterator = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
    
    return !$iterator->valid();
  }
  
  public static function isFile(string $file, bool $isRoot = true):bool
  {
    if ('' === $file) {
      
      return false;
    }
    
    $filename = $isRoot ? $_SERVER['DOCUMENT_ROOT'].$file : $file;
    
    return file_exists($filename) && !is_dir($filename);
  }
  
  public static function parse(
    array | string $input, FileType $fileType = FileType::JSON, bool $isObjectResult = true, bool $isRoot = true):mixed
  {
    $files = is_array($input) ? $input : (is_string($input) ? [$input] : []);
    
    if (empty($files)) {
      
      throw new \Exception('Empty files', -1);
    }
    
    $resultArray = [];
    $resultString = '';
    $xmlElements = [];
    
    foreach ($files as $file) {
      if (!self::isFile($file, $isRoot)) {
        
        throw new \Exception("File $file not found", -2);
      }
      
      $filePath = $isRoot ? $_SERVER['DOCUMENT_ROOT'].$file : $file;
      
      $rawContent = file_get_contents($filePath);
      
      if (false === $rawContent) {
        
        continue;
      }
      
      switch ($fileType) {
        case FileType::JSON:
          $fileContent = json_decode($rawContent, true);
          
          if (is_array($fileContent)) {
            $resultArray = array_merge_recursive($resultArray, $fileContent);
          }
          
          break;
        
        case FileType::INI:
          $fileContent = parse_ini_file($filePath, true);
          
          if (is_array($fileContent)) {
            $resultArray = array_merge_recursive($resultArray, $fileContent);
          }
          
          break;
        
        case FileType::SERIALIZE:
          $fileContent = @unserialize($rawContent);
          
          if (false !== $fileContent && is_array($fileContent)) {
            $resultArray = array_merge_recursive($resultArray, $fileContent);
          }
          
          break;
        
        case FileType::XML:
          $fileContent = simplexml_load_string($rawContent);
          
          if ($fileContent instanceof \SimpleXMLElement) {
            $xmlElements[] = $fileContent;
          }
          
          break;
        
        case FileType::ANY:
          $resultString .= $rawContent;
      }
      
      unset($rawContent, $fileContent);
    }
    
    if (FileType::XML === $fileType) {
      if (empty($xmlElements)) {
        
        return null;
      }
      
      return 1 === count($xmlElements) ? $xmlElements[0] : $xmlElements;
    }
    
    if (FileType::ANY === $fileType) {
      
      return $resultString;
    }
    
    if ($isObjectResult) {
      
      return empty($resultArray) ? new \stdClass() : json_decode(json_encode($resultArray, JSON_FORCE_OBJECT));
    }
    
    return $resultArray;
  }
}
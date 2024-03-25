<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\Extensions;
use PhpOffice\PhpWord\{Exception\Exception, Settings, TemplateProcessor};

final class Word
{
  public static bool $debug = false;
  
  private \DateTime $dateTime;
  
  private string $fileCopy;
  
  private string $fileDownload;
  
  private Extensions $fileExtIn = Extensions::DOCX;
  
  private Extensions $fileExtOut = Extensions::DOCX;
  
  private array $fileImages = [];
  
  private string $fileRead;
  
  private string $fileTemplate;
  
  private bool $isConvert = false;
  
  private bool $isLinks = false;
  
  private string $path = "\\files\\templates\\";
  
  private string $pathImage = "\\files\\templates\\images\\";
  
  private bool $testMode = false;
  
  private ?TemplateProcessor $word;
  
  private \SimpleXMLElement $xml;
  
  public function __construct(\SimpleXMLElement $xml, bool $testMode = false)
  {
    $this->xml = $xml;
    
    if (!empty($this->xml['err'])) {
      
      throw new \Exception("Помилка формування даних [{$this->xml['err']}]", -1);
    }
    
    if (empty($this->xml->данные)) {
      
      throw new \Exception("Відсутні дані для друку", -2);
    }
    
    if (empty($this->xml->шаблон)) {
      
      throw new \Exception("Відсутній шаблон документа", -3);
    }
    
    $directory = trim($this->xml->шаблон['каталог'] ?? '');
    $directory = '' === $directory ? '' : $directory.DIRECTORY_SEPARATOR;
    
    $this->fileTemplate = $_SERVER['DOCUMENT_ROOT'];
    $this->fileTemplate .= $this->path;
    $this->fileTemplate .= $directory;
    $this->fileTemplate .= $this->xml->шаблон;
    $this->fileTemplate .= $this->fileExtIn->value;
    
    if (!File::isFile($this->fileTemplate, false)) {
      
      throw new \Exception("Не знайдено шаблону документа [$directory{$this->xml->шаблон}".$this->fileExtIn->value.']',
        -4);
    }
    
    $this->isConvert = !TEST_SERVER
      && 'pdf' === mb_convert_case(trim($this->xml->данные->ext ?? 'docx'), MB_CASE_LOWER);
    
    $this->dateTime = new \DateTime();
    $this->fileExtOut = $this->isConvert ? Extensions::PDF : $this->fileExtOut;
    $tmpPath = sys_get_temp_dir();
    $tmpFile = "$tmpPath/{$this->xml->шаблон}_".$this->dateTime->format('YmdHisu');
    
    $this->fileCopy = $tmpFile.$this->fileExtIn->value;
    $this->fileRead = $tmpFile.$this->fileExtOut->value;
    $this->fileDownload = trim($this->xml->данные->name ?? '');
    $this->fileDownload = '' === $this->fileDownload ? "Document ".$this->dateTime->format('YmdHis')
      : $this->fileDownload;
    $this->fileDownload .= $this->fileExtOut->value;
    
    $this->isLinks = isset($xml->ссылки) && $xml->ссылки->ссылка->count();
    
    $this->testMode = $testMode;
    
    if (self::$debug) {
      trigger_error(__METHOD__." Request\n".$this->xml->saveXML()."\nFile template: ".$this->fileTemplate
        ."\nFile copy: ".$this->fileCopy."\nFile download: ".$this->fileDownload."\nFile read: ".$this->fileRead
        ."\nisConvert: ".($this->isConvert ? 'yes' : 'no')."\ntestMode: ".($this->testMode ? 'yes' : 'no'));
    }
  }
  
  public function download():void
  {
    if (!File::isFile($this->fileRead, false)) {
      
      throw new \Exception('Відсутній файл для виводу', -50);
    }
    
    if (ob_get_level()) {
      ob_end_clean();
    }
    
    header("Content-Description: File Transfer");
    header("Content-Type: ".$this->fileExtOut->getContentType());
    header("Content-Disposition: attachment; filename=\"$this->fileDownload\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".filesize($this->fileRead));
    header("Expires: 0");
    header("Cache-Control: must-revalidate");
    header("Pragma: public");
    
    if (self::$debug) {
      trigger_error(__METHOD__." Read file $this->fileRead");
    }
    
    readfile($this->fileRead);
    
    self::_deleteTmpFile();
  }
  
  public function fill():void
  {
    if (!isset($this->xml->данные->data) || !$this->xml->данные->data->children()) {
      
      throw new \Exception('Відсутні дані для виводу', -20);
    }
    
    foreach ($this->xml->данные->data->children() as $child) {
      $alias = $child->getName();
      $param = (string)$child;
      $col = trim($child['col'] ?? '');
      
      if (empty($alias) || empty($param)) {
        
        continue;
      }
      
      $node = $this->xml->xpath($param);
      $cntNode = count($node);
      
      if (!$cntNode) {
        
        continue;
      }
      
      if ('' !== $col) {
        try {
          $this->word->cloneRow("$col", $cntNode);
          self::_replaceNodeValue($alias, $node, $col);
        } catch (Exception) {
        }
        
        $cloneBlock = $this->word->cloneBlock("b$col", $cntNode, true, true);
        
        if (!empty($cloneBlock)) {
          self::_replaceNodeValue($alias, $node, $col);
        }
        
        continue;
      }
      
      self::_replaceNodeValue($alias, $node, $col);
    }
    
    if (isset($this->xml->данные->images)) {
      self::_replaceImages();
    }
  }
  
  public function load():void
  {
    Settings::setOutputEscapingEnabled(true);
    
    try {
      $this->word = new TemplateProcessor($this->fileTemplate);
    } catch (Exception) {
      
      throw new Exception('Помилка при відкритті шаблону документа', -10);
    }
  }
  
  public function removeVar():void
  {
    if (!$this->testMode) {
      $this->word->removeVar();
    }
  }
  
  public function save():void
  {
    $this->word->saveAs($this->fileCopy);
    $this->word = null;
    
    if (!File::isFile($this->fileCopy, false)) {
      
      throw new \Exception('Помилка створення копії шаблону файла', -30);
    }
    
    if ($this->isLinks) {
      self::_links();
    }
    
    if ($this->isConvert) {
      self::_convert();
    }
  }
  
  private function _convert():void
  {
    $tmpPath = sys_get_temp_dir();
    $tempLibreOfficeProfile = $tmpPath."\\LibreOfficeProfile\\".$this->dateTime->format('YmdHisu');
    $command = "\"C:\Program Files\LibreOffice\program\soffice.exe\" -env:UserInstallation=file:///".str_replace("\\",
        "/", $tempLibreOfficeProfile)." --headless --convert-to pdf --outdir $tmpPath \"$this->fileCopy\"";
    
    if (self::$debug) {
      trigger_error(__METHOD__." run command:\n$command", E_USER_DEPRECATED);
    }
    
    exec($command, $output, $return);
    exec('rmdir /S /Q "'.$tempLibreOfficeProfile.'"');
    
    if (!empty($return)) {
      
      throw new \Exception('Помилка виконання команди конвертації', -40);
    }
  }
  
  private function _deleteTmpFile():void
  {
    if (File::isFile($this->fileRead, false)) {
      unlink($this->fileRead);
    }
    
    if (File::isFile($this->fileCopy, false)) {
      unlink($this->fileCopy);
    }
    
    foreach ($this->fileImages as $fileImage) {
      if (File::isFile($fileImage)) {
        unlink($fileImage);
      }
    }
  }
  
  private function _links():void
  {
    $zip = new \ZipArchive();
    
    if (!$zip->open($this->fileCopy, 0)) {
      $zip->close();
      
      return;
    }
    
    $relsName = 'word/_rels/document.xml.rels';
    $xmlStr = $zip->getFromName($relsName);
    
    foreach ($this->xml->ссылки->ссылка as $link) {
      $linkID = trim($link['id'] ?? '');
      
      if ('' === $linkID) {
        
        continue;
      }
      
      $xmlStr = str_replace("link_$linkID", htmlspecialchars((string)$link, ENT_XML1), $xmlStr);
    }
    
    $zip->addFromString($relsName, $xmlStr);
    $zip->close();
  }
  
  private function _replaceImages():void
  {
    $pathImage = $_SERVER['DOCUMENT_ROOT'].$this->pathImage;
    
    foreach ($this->xml->данные->images->image ?? [] as $image) {
      $imageName = trim($image['name'] ?? '');
      
      if ('' === $imageName) {
        
        continue;
      }
      
      $fileImage = '' === trim($image) ? '' : (false === stripos($image, ':') ? $pathImage.$image : $image);
      $fileImageDefault = '' === trim($image['def'] ?? '') ? '' : $pathImage.$image['def'];
      $fileImageDefault2 = '' === trim($image['def_2'] ?? '') ? '' : $pathImage.$image['def_2'];
      
      if (self::$debug) {
        trigger_error(__METHOD__
          ."\nImage: $fileImage\nImage Default: $fileImageDefault\nImage Default2: $fileImageDefault2",
          E_USER_DEPRECATED);
      }
      
      if (File::isFile($fileImage, false)) {
        $this->word->setImageValue([$imageName], [
          'path' => $fileImage,
          'width' => $image['width'] ?? '',
          'height' => $image['height'] ?? '',
          'ratio' => (bool)($image['ratio'] ?? true)
        ]);
      } elseif (File::isFile($fileImageDefault, false)) {
        $this->word->setImageValue([$imageName], [
          'path' => $fileImageDefault,
          'width' => $image['width'] ?? '',
          'height' => $image['height'] ?? '',
          'ratio' => (bool)($image['ratio'] ?? true)
        ]);
      } elseif (File::isFile($fileImageDefault2, false)) {
        $this->word->setImageValue([$imageName], [
          'path' => $fileImageDefault2,
          'width' => $image['width'] ?? '',
          'height' => $image['height'] ?? '',
          'ratio' => (bool)($image['ratio'] ?? true)
        ]);
      }
      
      $this->word->setValue($imageName, '');
      
      # Тимчасові зображення
      if (isset($image['remove']) && $fileImage) {
        $this->fileImages[] = $fileImage;
      }
    }
  }
  
  private function _replaceNodeValue(
    string $alias, array $node, string $col = ''):void
  {
    $countable = (int)('' !== $col);
    $count = $countable;
    
    foreach ($node as $item) {
      $postfix = $countable ? "#$count" : '';
      
      foreach ($item as $key => $value) {
        
        if (self::$debug) {
          trigger_error("Replace $alias.$key$postfix = ".trim($value), E_USER_DEPRECATED);
        }
        
        $isEscape = false === mb_stripos($value, '<w:br/>');
        
        if (!$isEscape) {
          Settings::setOutputEscapingEnabled(false);
        }
        
        $this->word->setValue("$alias.$key$postfix", trim($value));
        
        # Видалення початку таблиці або блоку
        if ($countable) {
          $this->word->setValue("$col$postfix", '');
        }
        
        if (!$isEscape) {
          Settings::setOutputEscapingEnabled(true);
        }
      }
      
      $count += $countable;
    }
  }
}
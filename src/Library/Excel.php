<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\{Decrypt, Extensions, SQLAnywhere};
use PhpOffice\PhpSpreadsheet\{Cell\DataType, Exception, IOFactory, Spreadsheet, Style\Alignment, Style\Border,
  Style\Fill, Style\NumberFormat, Worksheet\Worksheet
};

final class Excel
{
  public array $request = [];
  public static ?object $settings;
  public ?Spreadsheet $spreadsheet = null;
  private array $dateColumns = [];
  private \DateTime $dateTime;
  private string $fileDownload;
  private ?Extensions $fileExt = Extensions::XLS;
  private string $highestColumn = 'A';
  private static bool $isDebug = false;
  private array $numericColumns = [];
  private int $posX = 1;
  private int $posY = 1;
  private array | null | \SimpleXMLElement $queryResult;
  private SQLAnywhere $queryType = SQLAnywhere::FETCH_ALL;
  private int $rowFirst = 1;
  private Worksheet $sheet;
  private float $startTime;
  
  public function __construct(array $request = [], bool $isDebug = false)
  {
    if (empty($request)) {
      
      throw new \Exception("Відсутні дані для друку", -1);
    }
    
    $this->request = $request;
    
    # Decode request Windows-1251 to UTF-8
    if (!empty($request['d'])) {
      foreach ($this->request as $key => $value) {
        $this->request[$key] = '' === $value ? '' : Encoding::decode(trim($value));
      }
    }
    
    if (!empty($request['e'])) {
      foreach ($this->request as $key => $value) {
        if (in_array($key, [
          'c',
          'e',
          'p'
        ], true)) {
          
          continue;
        }
        
        $this->request[$key] = '' === $value ? '' : urldecode(Crypt::action(trim($value), Decrypt::BASE));
      }
    }
    
    if (empty($this->request['q'])) {
      
      throw new \Exception("Не передано обов'язковий параметр", -2);
    }
    
    self::$isDebug = $isDebug;
    
    if (self::$isDebug) {
      $this->startTime = microtime(true);
      
      trigger_error(__METHOD__." Request\n".json_encode($request,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), E_USER_ERROR);
    }
    
    $this->dateTime = new \DateTime();
    
    $defaultFilename = "Export_Excel__".$this->dateTime->format('YmdHisu').$this->fileExt->value;
    
    $this->fileDownload = '' === ($this->request['f'] ?? '') ? $defaultFilename : $this->request['f'];
    
    $detectedExt = Extensions::tryFrom('.'.Str::lowerCase(pathinfo($this->fileDownload, PATHINFO_EXTENSION)));
    
    if ($detectedExt) {
      $this->fileExt = $detectedExt;
    } else {
      throw new \Exception("Не визначено тип вихідного файлу", -4);
    }
  }
  
  public function download():void
  {
    try {
      $objectWriter = IOFactory::createWriter($this->spreadsheet,
        mb_convert_case(trim($this->fileExt->value, '.'), MB_CASE_TITLE));
    } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
      
      throw new \Exception($e->getMessage(), -100);
    }
    
    $firstCell = "A".($this->rowFirst + 1);
    
    $this->sheet->setSelectedCells($firstCell);
    $this->spreadsheet->getActiveSheet()
                      ->freezePane($firstCell);
    
    header('Content-type: '.$this->fileExt->getContentType());
    header('Content-Disposition: attachment; filename='.$this->fileDownload);
    header('Expires: 0');
    header("Last-Modified: ".gmdate('D,d M YH:i:s').' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    if (self::$isDebug) {
      $totalTime = round(microtime(true) - $this->startTime, 4);
      
      trigger_error(__METHOD__." Preparing download finished. Total execution time: $totalTime sec.", E_USER_ERROR);
    }
    
    $objectWriter->save('php://output');
  }
  
  public function fill():void
  {
    $fillStart = microtime(true);
    $title = trim($this->request['t'] ?? '');
    
    if ('' !== $title) {
      $this->rowFirst++;
      $this->posY++;
    }
    
    if (SQLAnywhere::COLUMN === $this->queryType) {
      $this->fillXML();
    } else {
      $this->fillArray();
    }
    
    $this->queryResult = null;
    
    $highestRow = $this->sheet->getHighestRow();
    
    foreach ($this->numericColumns as $colIdx) {
      $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
      $this->sheet->getStyle($colLetter.($this->rowFirst + 1).':'.$colLetter.$highestRow)
                  ->getNumberFormat()
                  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    }
    
    foreach ($this->dateColumns as $colIdx) {
      $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
      $this->sheet->getStyle($colLetter.($this->rowFirst + 1).':'.$colLetter.$highestRow)
                  ->getNumberFormat()
                  ->setFormatCode('dd.mm.yyyy');
    }
    
    $this->sheet->getStyle('A'.$this->rowFirst.':'.$this->highestColumn.$highestRow)
                ->applyFromArray([
                  'borders' => [
                    'allBorders' => [
                      'borderStyle' => Border::BORDER_THIN
                    ],
                  ]
                ]);
    
    $this->sheet->setAutoFilter('A'.$this->rowFirst.':'.$this->highestColumn.$highestRow);
    
    foreach ($this->sheet->getColumnIterator() as $column) {
      $this->sheet->getColumnDimension($column->getColumnIndex())
                  ->setAutoSize(true);
    }
    
    if ('' !== $title) {
      $this->sheet->setCellValue('A1', $title);
      $this->sheet->getStyle('A1')
                  ->getFont()
                  ->setBold(true);
      $this->sheet->getStyle('A1')
                  ->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                  ->setVertical(Alignment::VERTICAL_CENTER);
      $this->sheet->getRowDimension('1')
                  ->setRowHeight(30);
      
      try {
        $this->sheet->mergeCells('A1:'.$this->highestColumn.'1');
      } catch (Exception $e) {
        trigger_error(__METHOD__." mergeCells error\n".$e->getMessage());
      }
    }
    
    if (self::$isDebug) {
      $fillTime = round(microtime(true) - $fillStart, 4);
      
      trigger_error(__METHOD__." Grid filling finished in: $fillTime sec.", E_USER_ERROR);
    }
  }
  
  public function load():void
  {
    $loadStart = microtime(true);
    
    Config::load(['dbase.php']);
    
    self::$settings = Config::getAsObject();
    
    if (empty(self::$settings)) {
      
      throw new \Exception("Помилка налаштувань [dbase]", -3);
    }
    
    $dbaseName = 'dbase'.(defined('TEST_SERVER') && TEST_SERVER ? 'Test' : '');
    $connection = self::$settings->dbase->{$dbaseName} ?? null;
    
    if (is_null($connection)) {
      
      throw new \Exception("Не визначено з'єднання із базою даних", -4);
    }
    
    $connection = (int)($this->request['c'] ?? $connection);
    
    $this->queryType = empty($this->request['p']) ? SQLAnywhere::COLUMN : $this->queryType;
    
    DbSQLAnywhere::disconnect();
    $result = DbSQLAnywhere::query($this->request['q'], $this->queryType, $connection, flags: SQL_KEY_CASE_ORIGIN);
    DbSQLAnywhere::disconnect();
    
    if (empty($result)) {
      
      throw new \Exception("Відсутні дані для формування", -10);
    }
    
    $success = true;
    
    if (SQLAnywhere::COLUMN === $this->queryType) {
      try {
        $result = Str::replaceHeader($result);
        $this->queryResult = new \SimpleXMLElement($result);
        
        if (!$this->queryResult->table || !$this->queryResult->table->row->count()) {
          $success = false;
        }
      } catch (\Exception) {
        $success = false;
      }
    } else {
      $this->queryResult = $result;
    }
    
    $success = $success && !empty($this->queryResult);
    
    if (self::$isDebug) {
      trigger_error(__METHOD__." Query [".$this->request['q']."]\nConnection [$connection] Query type ["
        .$this->queryType->name."] Query has result [$success]", E_USER_ERROR);
    }
    
    if (!$success) {
      
      throw new \Exception("Помилка отримання даних", -20);
    }
    
    try {
      $this->spreadsheet = new Spreadsheet();
    } catch (\Exception) {
      
      throw new \Exception("Помилка створення таблиці", -30);
    }
    
    $this->spreadsheet->getProperties()
                      ->setCreator('PHPSpreadsheet')
                      ->setLastModifiedBy('PHPSpreadsheet')
                      ->setTitle('Excel Document')
                      ->setSubject('PHPSpreadsheet Excel Document')
                      ->setDescription('PHPSpreadsheet Excel Document')
                      ->setKeywords('PHPSpreadsheet Excel Document')
                      ->setCategory('PHPSpreadsheet Excel Document');
    
    try {
      $this->spreadsheet->setActiveSheetIndex(0)
                        ->setTitle('' === ($this->request['s'] ?? '') ? 'Лист' : $this->request['s']);
    } catch (\Exception $e) {
      
      throw new \Exception($e->getMessage(), -40);
    }
    
    $this->sheet = $this->spreadsheet->getActiveSheet();
    
    if (self::$isDebug) {
      $loadTime = round(microtime(true) - $loadStart, 4);
      
      trigger_error(__METHOD__." Query & Fetch took: $loadTime sec.", E_USER_ERROR);
    }
  }
  
  private function fillArray():void
  {
    foreach ($this->queryResult as $row) {
      if ($this->posY === $this->rowFirst) {
        foreach (array_keys($row) as $item) {
          $this->sheet->setCellValueExplicit([
            $this->posX,
            $this->rowFirst
          ], trim($item), DataType::TYPE_STRING);
          $this->posX++;
        }
        
        $this->fillShape();
      }
      
      $this->posX = 1;
      $this->posY++;
      
      foreach ($row as $value) {
        $this->fillValue([
          $this->posX,
          $this->posY
        ], trim($value));
        $this->posX++;
      }
    }
  }
  
  private function fillShape():void
  {
    $this->highestColumn = $this->sheet->getHighestColumn();
    
    $this->sheet->getStyle('A'.$this->rowFirst.':'.$this->highestColumn.$this->rowFirst)
                ->applyFromArray([
                  'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                  ],
                  'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => [
                      'rgb' => '000059'
                    ]
                  ],
                  'font' => [
                    'bold' => true,
                    'color' => [
                      'rgb' => 'ffffff'
                    ]
                  ]
                ]);
  }
  
  private function fillValue(array $coordinate, string $value):void
  {
    $colIndex = $coordinate[0];
    
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", substr($value, 0, 10))) {
      $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', substr($value, 0, 10).' 00:00:00');
      
      if ($dateObj) {
        $this->sheet->setCellValue($coordinate, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateObj));
        
        if (!in_array($colIndex, $this->dateColumns, true)) {
          $this->dateColumns[] = $colIndex;
        }
      } else {
        $this->sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
      }
    } elseif (is_numeric($value) && is_float($value * 1)) {
      $this->sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_NUMERIC);
      
      if (!in_array($colIndex, $this->numericColumns, true)) {
        $this->numericColumns[] = $colIndex;
      }
    } else {
      $this->sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
    }
  }
  
  private function fillXML():void
  {
    foreach ($this->queryResult->table->row ?? [] as $row) {
      $this->posX = 1;
      
      foreach ($row->td ?? [] as $td) {
        $this->fillValue([
          $this->posX,
          $this->posY
        ], trim($td));
        $this->posX++;
      }
      
      if ($this->posY === $this->rowFirst) {
        $this->fillShape();
      }
      
      $this->posY++;
    }
  }
}
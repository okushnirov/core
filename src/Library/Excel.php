<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\{Decrypt, Extensions, SQLAnywhere};
use PhpOffice\PhpSpreadsheet\{Cell\DataType, Exception, IOFactory, Spreadsheet, Style\Alignment, Style\Border,
  Style\Fill, Style\NumberFormat, Worksheet\Worksheet
};

final class Excel
{
  public static bool $debug = false;
  
  public array $request = [];
  
  public static mixed $settings;
  
  public ?Spreadsheet $spreadsheet;
  
  private string $fileDownload;
  
  private \DateTime $dateTime;
  
  private ?Extensions $fileExt = Extensions::XLS;
  
  private array | null | \SimpleXMLElement $queryResult;
  
  private SQLAnywhere $queryType = SQLAnywhere::FETCH_ALL;
  
  public int $rowFirst = 1;
  
  private Worksheet $sheet;
  
  private string $highestColumn;
  
  private int $posX = 1;
  
  private int $posY = 1;
  
  public function __construct(array $request = [])
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
    
    # Encoding request
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
    
    if (self::$debug) {
      trigger_error(__METHOD__." Request\n".json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    try {
      self::$settings = File::parse([
        '/site.webmanifest',
        '/json/dbase.json'
      ]);
    } catch (\Exception $e) {
      $errorMessage = 'No error' === $e->getMessage() ? '' : $e->getMessage();
      
      throw new \Exception("Помилка налаштувань [$errorMessage]", -3);
    }
    
    $this->dateTime = new \DateTime();
    
    $this->fileDownload = '' === ($this->request['f'] ?? '') ? "Export_Excel_".$this->dateTime->format('YmdHisu')
      .$this->fileExt->value : $this->request['f'];
    $this->fileExt = Extensions::tryFrom('.'.Str::lowerCase(pathinfo($this->fileDownload, PATHINFO_EXTENSION)));
    
    if (!$this->fileExt) {
      
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
    
    $objectWriter->save('php://output');
  }
  
  public function fill():void
  {
    $title = trim($this->request['t'] ?? '');
    
    if ('' !== $title) {
      $this->rowFirst++;
      $this->posY++;
    }
    
    if (SQLAnywhere::COLUMN === $this->queryType) {
      self::_fillXML();
    } else {
      self::_fillArray();
    }
    
    $this->queryResult = null;
    
    $highestRow = $this->sheet->getHighestRow();
    
    # Стилі комірки
    $this->sheet->getStyle('A'.$this->rowFirst.':'.$this->highestColumn.$highestRow)
                ->applyFromArray([
                  'borders' => [
                    'allBorders' => [
                      'borderStyle' => Border::BORDER_THIN
                    ],
                  ]
                ]);
    
    # Автофільтр
    $this->sheet->setAutoFilter('A'.$this->rowFirst.':'.$this->highestColumn.$highestRow);
    
    # Автоматический размер
    foreach (range('A', $this->highestColumn) as $col) {
      $this->sheet->getColumnDimension($col)
                  ->setAutoSize(true);
    }
    
    if ('' !== $title) {
      # Заголовок таблиці
      $this->sheet->setCellValue('A1', $title);
      $this->sheet->getStyle('A1')
                  ->getFont()
                  ->setBold(true);
      $this->sheet->getStyle('A1')
                  ->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_LEFT);
      
      # Об'єднати комірки заголовка
      try {
        $this->sheet->mergeCells('A1:'.$this->highestColumn.'1');
      } catch (Exception $e) {
        trigger_error($e->getMessage());
      }
    }
  }
  
  public function load():void
  {
    $this->queryType = empty($this->request['p']) ? SQLAnywhere::COLUMN : $this->queryType;
    
    $connection = self::$settings->dbase->{'dbase'.(TEST_SERVER ? 'Test' : '')};
    $connection = (int)($this->request['c'] ?? $connection);
    
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
    
    if (self::$debug) {
      trigger_error(__METHOD__." Query [".$this->request['q']."]\nConnection [$connection] Query type ["
        .$this->queryType->name."] Query has result [$success]");
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
  }
  
  private function _fillArray():void
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
        
        $this->posY++;
      }
      
      $this->posX = 1;
      
      foreach ($row as $value) {
        self::_fillValue([
          $this->posX,
          $this->posY
        ], trim($value));
        $this->posX++;
      }
      
      $this->posY++;
    }
  }
  
  private function _fillXML():void
  {
    foreach ($this->queryResult->table->row ?? [] as $row) {
      $this->posX = 1;
      
      foreach ($row->td ?? [] as $td) {
        self::_fillValue([
          $this->posX,
          $this->posY
        ], trim($td));
        $this->posX++;
      }
      
      $this->posY++;
    }
  }
  
  private function _fillValue(array $coordinate, string $value):void
  {
    # DATE
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", substr($value, 0, 10))) {
      $this->sheet->setCellValue($coordinate,
        \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(\DateTime::createFromFormat('Y-m-d H:i:s',
          $value.' 00:00:00')))
                  ->getStyle($coordinate)
                  ->getNumberFormat()
                  ->setFormatCode('dd.mm.yyyy');
    } # NUMBER
    elseif (is_numeric($value) && is_float($value * 1)) {
      $this->sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_NUMERIC)
                  ->getStyle($coordinate)
                  ->getNumberFormat()
                  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    } # TEXT
    else {
      $this->sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
    }
  }
}
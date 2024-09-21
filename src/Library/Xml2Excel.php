<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\{DateEn, Extensions};
use PhpOffice\PhpSpreadsheet\{Cell\DataType, Exception, IOFactory, Spreadsheet, Style\Alignment, Style\Border,
  Style\Fill, Style\NumberFormat, Worksheet\Worksheet
};
use JetBrains\PhpStorm\ArrayShape;

final class Xml2Excel
{
  public static bool $debug = false;
  
  public ?Spreadsheet $spreadsheet;
  
  private array $colGroup = [];
  
  private array $defaults = [
    'bgThead' => 'd0cece',
    'bgTfoot' => 'f2f2f2'
  ];
  
  private string $fileDownload;
  
  private ?Extensions $fileExt = Extensions::XLS;
  
  private bool $isAutofilter = true;
  
  private bool $isAutosize = true;
  
  private bool $isFreeze = true;
  
  private bool $isBorder = true;
  
  private bool $isCaption = false;
  
  private bool $isTbody = false;
  
  private bool $isTfoot = false;
  
  private bool $isThead = false;
  
  private Worksheet $sheet;
  
  private string $highestColumn = '';
  
  private int $posX = 1;
  
  private int $posY = 1;
  
  private string $title = 'Аркуш';
  
  private \SimpleXMLElement $xml;
  
  public function init(\SimpleXMLElement $xml):void
  {
    if ('table' !== $xml->getName()) {
      
      throw new \Exception("Відсутні вхідні дані", -1);
    }
    
    $this->xml = $xml;
    
    unset($xml);
    
    # Тип XLS|XLSX
    $fileExt = Str::lowerCase(trim($this->xml['ext'] ?? ''));
    
    if ('' !== $fileExt && Extensions::tryFrom(".$fileExt")) {
      $this->fileExt = Extensions::tryFrom(".$fileExt");
    }
    
    if (!$this->fileExt) {
      
      throw new \Exception("Не визначено тип вихідного файлу", -2);
    }
    
    # Назва файла
    $fileDownload = trim($this->xml['filename'] ?? '');
    $this->fileDownload = '' === $fileDownload ? "Export_Excel_".(new \DateTime())->format('YmdHisu') : $fileDownload;
    $this->fileDownload .= $this->fileExt->value;
    
    # Автоматичний фільтр
    $autofilter = trim($this->xml['autofilter'] ?? '');
    $this->isAutofilter = '' === $autofilter ? $this->isAutofilter : (bool)(int)$autofilter;
    
    # Автоматичний розмір
    $autosize = trim($this->xml['autosize'] ?? '');
    $this->isAutosize = '' === $autosize ? $this->isAutosize : (bool)(int)$autosize;
    
    # Рамки таблиці
    $border = trim($this->xml['border'] ?? '');
    $this->isBorder = '' === $border ? $this->isBorder : (bool)(int)$border;
    
    # Закріплення області
    $freeze = trim($this->xml['freeze'] ?? '');
    $this->isFreeze = '' === $freeze ? $this->isFreeze : (bool)(int)$freeze;
    
    # Назва аркуша
    $title = trim($this->xml->sheet ?? $this->xml->scheet ?? '');
    $this->title = '' === $title ? $this->title : $title;
    
    # Чи існує заголовок
    $this->isCaption = '' !== trim($this->xml->caption[0] ?? '');
    
    # Чи існують дані таблиці
    $this->isTbody = isset($this->xml->tbody->tr) && 0 < ($this->xml->tbody->tr[0]->td->count() ?? 0);
    
    # Чи існує футер таблиці
    $this->isTfoot = isset($this->xml->tfoot->tr) && 0 < ($this->xml->tfoot->tr[0]->td->count() ?? 0);
    
    # Чи існує шапка таблиці
    $this->isThead = isset($this->xml->thead->tr) && 0 < ($this->xml->thead->tr[0]->th->count() ?? 0);
    
    # Налаштування комірок
    if (isset($this->xml->colgroup)) {
      $this->colGroup();
      $this->posX = 1;
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__."\n".json_encode([
          'ext' => $this->fileExt->value,
          'filename' => $this->fileDownload,
          'isAutofilter' => $this->isAutofilter,
          'isAutosize' => $this->isAutosize,
          'isBorder' => $this->isBorder,
          'isCaption' => $this->isCaption,
          'isFreeze' => $this->isFreeze,
          'isTfoot' => $this->isTfoot,
          'isThead' => $this->isThead,
          'title' => $this->title,
          'colGroup' => $this->colGroup
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    # Створення аркуша
    $this->spreadsheet = new Spreadsheet();
    $this->spreadsheet->getProperties()
                      ->setCreator('PHPSpreadsheet')
                      ->setLastModifiedBy('PHPSpreadsheet')
                      ->setTitle('Excel Document')
                      ->setSubject('Excel Document')
                      ->setDescription('Excel Document')
                      ->setKeywords('Excel Document')
                      ->setCategory('PHPSpreadsheet excel file');
    
    # Аркуш
    try {
      $this->spreadsheet->setActiveSheetIndex(0)
                        ->setTitle($this->title);
    } catch (\Exception $e) {
      
      throw new \Exception($e->getMessage(), -3);
    }
    
    $this->sheet = $this->spreadsheet->getActiveSheet();
  }
  
  public function download():void
  {
    try {
      $objectWriter = IOFactory::createWriter($this->spreadsheet,
        mb_convert_case(trim($this->fileExt->value, '.'), MB_CASE_TITLE));
    } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
      
      throw new \Exception($e->getMessage(), -100);
    }
    
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
    if ($this->isThead) {
      $this->posY = 1 + (int)$this->isCaption;
      $this->thead();
      $this->highestColumn = $this->sheet->getHighestColumn($this->posY);
    }
    
    if ($this->isTbody) {
      $this->posY++;
      $this->tbody();
    }
    
    if ($this->isTfoot) {
      $this->tfoot();
    }
    
    $this->highestColumn = '' === $this->highestColumn ? $this->sheet->getHighestColumn($this->posY)
      : $this->highestColumn;
    
    if ($this->isCaption) {
      $this->caption();
    }
    
    $firstRow = (int)$this->isCaption + (int)$this->isThead;
    $highestRow = $this->sheet->getHighestRow();
    
    # Автофільтр
    if ($this->isAutofilter && $this->isThead && $this->isTbody && '' !== $this->highestColumn && $highestRow) {
      $highestRow -= (int)$this->isTfoot;
      
      $this->sheet->setAutoFilter('A'.$firstRow.':'.$this->highestColumn.$highestRow);
    }
    
    # Автоматичний розмір
    if ($this->isAutosize && '' !== $this->highestColumn) {
      foreach (range('A', $this->highestColumn) as $col) {
        $this->sheet->getColumnDimension($col)
                    ->setAutoSize(true);
      }
    }
    
    # Встановити курсор на першу строку після заголовку
    if (1 !== $firstRow) {
      $firstCell = "A".($firstRow + 1);
      $this->sheet->setSelectedCells($firstCell);
      
      # Закріплення області
      if ($this->isFreeze) {
        $this->spreadsheet->getActiveSheet()
                          ->freezePane($firstCell);
      }
    }
  }
  
  public function save():void
  {
    try {
      $objectWriter = IOFactory::createWriter($this->spreadsheet,
        mb_convert_case(trim($this->fileExt->value, '.'), MB_CASE_TITLE));
    } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
      
      throw new \Exception($e->getMessage(), -100);
    }
    
    header('Content-type: '.$this->fileExt->getContentType());
    header('Expires: 0');
    header("Last-Modified: ".gmdate('D,d M YH:i:s').' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    $objectWriter->save('php://output');
  }
  
  private function alignHorizontal(string $align, string $default):string
  {
    
    return match (Str::lowerCase($align)) {
      'center' => Alignment::HORIZONTAL_CENTER,
      'left' => Alignment::HORIZONTAL_LEFT,
      'right' => Alignment::HORIZONTAL_RIGHT,
      'justify' => Alignment::HORIZONTAL_JUSTIFY,
      default => $default
    };
  }
  
  private function alignVertical(string $align):string
  {
    
    return match (Str::lowerCase($align)) {
      'bottom' => Alignment::VERTICAL_BOTTOM,
      'top' => Alignment::VERTICAL_TOP,
      'justify' => Alignment::VERTICAL_JUSTIFY,
      default => Alignment::VERTICAL_CENTER
    };
  }
  
  private function caption():void
  {
    $caption = $this->xml->caption[0];
    $captionStyle = $this->style($caption, [
      'align' => Alignment::HORIZONTAL_LEFT,
      'font' => 'bold',
      'height' => 24
    ]);
    
    $style = [
      'alignment' => [
        'horizontal' => $captionStyle['align'],
        'vertical' => $captionStyle['valign']
      ]
    ];
    
    if ('' !== $captionStyle['bg-color']) {
      $style['fill'] = [
        'color' => [
          'rgb' => $captionStyle['bg-color']
        ],
        'fillType' => Fill::FILL_SOLID
      ];
    }
    
    if ('' !== $captionStyle['color']) {
      $style['font'] = [
        'color' => [
          'rgb' => $captionStyle['color']
        ]
      ];
    }
    
    if ($captionStyle['bold']) {
      if (!isset($style['font'])) {
        $style['font'] = [];
      }
      
      $style['font']['bold'] = true;
    }
    
    if ($captionStyle['italic']) {
      if (!isset($style['font'])) {
        $style['font'] = [];
      }
      
      $style['font']['italic'] = true;
    }
    
    if ($captionStyle['underline']) {
      if (!isset($style['font'])) {
        $style['font'] = [];
      }
      
      $style['font']['underline'] = true;
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__."\n".json_encode(array_merge($captionStyle, [
          'style' => $style
        ]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), E_USER_ERROR);
    }
    
    # Заголовок
    $this->sheet->setCellValue('A1', trim($caption));
    
    # Стилі
    $this->sheet->getStyle([
      1,
      1
    ])
                ->applyFromArray($style);
    
    # Висота заголовку
    if (0 < $captionStyle['height']) {
      $this->sheet->getRowDimension('1')
                  ->setRowHeight($captionStyle['height']);
    }
    
    # Об'єднати комірки заголовка
    if ($captionStyle['merge'] && '' !== $this->highestColumn) {
      try {
        $this->sheet->mergeCells('A1:'.$this->highestColumn.'1');
      } catch (Exception $e) {
        trigger_error($e->getMessage());
      }
    }
    
    unset($caption, $captionStyle, $merge, $style);
  }
  
  private function colgroup():void
  {
    $this->colGroup['style'] = $this->style($this->xml->colgroup, ['border' => $this->isBorder]);
    
    foreach ($this->xml->colgroup->col ?? [] as $col) {
      $style = $this->style($col, $this->colGroup['style']);
      $type = $this->type(trim($col['type'] ?? ''));
      
      $style['align'] = '' === $style['align'] ? match ($type) {
        'date' => Alignment::HORIZONTAL_CENTER,
        'integer', 'numeric' => Alignment::HORIZONTAL_RIGHT,
        default => Alignment::HORIZONTAL_LEFT
      } : $style['align'];
      
      $this->colGroup['c'.$this->posX] = [
        'style' => $style,
        'type' => $type
      ];
      
      $this->posX++;
    }
  }
  
  private function color(string $rgb, string $default):string
  {
    if ('' === $rgb) {
      
      return Str::lowerCase(trim($default, '#'));
    }
    
    $color = Str::lowerCase(trim($rgb, '#'));
    
    return preg_match("([a-f0-9]{6})", $color) ? $color : $default;
  }
  
  private function setStyle(array | string $coordinate, array $style):void
  {
    $this->sheet->getStyle($coordinate)
                ->applyFromArray($style);
  }
  
  #[ArrayShape([
    'align' => "string",
    'bg-color' => "string",
    'bold' => "bool",
    'border' => "bool",
    'color' => "string",
    'font' => "string",
    'height' => "int",
    'italic' => "bool",
    'merge' => "bool",
    'underline' => "bool",
    'valign' => "string"
  ])] private function style(\SimpleXMLElement $col, array $default = []):array
  {
    $align = $this->alignHorizontal(trim($col['align'] ?? ''), $default['align'] ?? '');
    $border = trim($col['border'] ?? '');
    $bgColor = $this->color(trim($col['bg-color'] ?? ''), $default['bg-color'] ?? '');
    $color = $this->color(trim($col['color'] ?? ''), $default['color'] ?? '');
    $font = Str::lowerCase(trim($col['font'] ?? ''));
    $height = (int)($col['height'] ?? -1);
    $merge = trim($col['merge'] ?? '');
    $valign = $this->alignVertical(trim($col['valign'] ?? ''));
    
    return [
      'align' => $align,
      'bg-color' => $bgColor,
      'bold' => false !== mb_stripos('' === $font ? ($default['font'] ?? '') : $font, 'bold'),
      'border' => '' === $border ? ($default['border'] ?? false) : (bool)(int)$border,
      'color' => $color,
      'font' => $font,
      'height' => 0 < $height ? $height : (0 < (int)($default['height'] ?? -1) ? (int)$default['height'] : ''),
      'italic' => false !== mb_stripos('' === $font ? ($default['font'] ?? '') : $font, 'italic'),
      'merge' => '' === $merge || 0 < (int)$merge,
      'underline' => false !== mb_stripos('' === $font ? ($default['font'] ?? '') : $font, 'underline'),
      'valign' => $valign,
    ];
  }
  
  private function tbody():void
  {
    $posY = $this->posY;
    
    foreach ($this->xml->tbody->tr as $tr) {
      $this->posX = 1;
      
      foreach ($tr->td as $td) {
        $coordinate = [
          $this->posX,
          $this->posY
        ];
        $colStyles = $this->colGroup['c'.$this->posX] ?? [];
        $tdType = $colStyles['type'] ?? '';
        
        $this->value($tdType, $coordinate, trim($td));
        
        $this->posX++;
      }
      
      $this->posY++;
    }
    
    $highestColumn = $this->sheet->getHighestColumn($this->posY - 1);
    ++$highestColumn;
    
    $this->posX = 1;
    
    for ($col = 'A'; $col != $highestColumn; ++$col) {
      $coordinate = $col.$posY.':'.$col.($this->posY - 1);
      $colStyles = $this->colGroup['c'.$this->posX] ?? [];
      $colStyle = $colStyles['style'] ?? [];
      $colType = $colStyle['type'] ?? '';
      
      $colStyle['align'] = '' === ($colStyle['align'] ?? '') ? match ($colType) {
        'date' => Alignment::HORIZONTAL_CENTER,
        'integer', 'numeric' => Alignment::HORIZONTAL_RIGHT,
        default => Alignment::HORIZONTAL_LEFT
      } : $colStyle['align'];
      
      $style = [
        'alignment' => [
          'horizontal' => $this->alignHorizontal($colStyle['align'], Alignment::HORIZONTAL_LEFT),
        ]
      ];
      
      if ('' !== ($colStyle['valign'] ?? '')) {
        $style['alignment']['vertical'] = $this->alignVertical($colStyle['valign'] ?? Alignment::VERTICAL_CENTER);
      }
      
      if ('' !== ($colStyle['bg-color'] ?? '')) {
        $style['fill'] = [
          'fillType' => Fill::FILL_SOLID,
          'color' => [
            'rgb' => $this->color($colStyle['bg-color'], '')
          ]
        ];
      }
      
      if ('' !== ($colStyle['color'] ?? '')) {
        $style['font'] = [
          'color' => [
            'rgb' => $this->color($colStyle['color'], '')
          ]
        ];
      }
      
      if ($colStyle['bold'] ?? false) {
        if (!isset($style['font'])) {
          $style['font'] = [];
        }
        
        $style['font']['bold'] = true;
      }
      
      if ($colStyle['italic'] ?? false) {
        if (!isset($style['font'])) {
          $style['font'] = [];
        }
        
        $style['font']['italic'] = true;
      }
      
      if ($colStyle['underline'] ?? false) {
        if (empty($style['font'])) {
          $style['font'] = [];
        }
        
        $style['font']['underline'] = true;
      }
      
      if ($colStyle['border'] ?? true) {
        $style['borders'] = [
          'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
          ]
        ];
      }
      
      if (self::$debug) {
        trigger_error(__METHOD__." $coordinate\n".json_encode($style, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
          E_USER_ERROR);
      }
      
      $this->setStyle($coordinate, $style);
      
      $this->posX++;
    }
  }
  
  private function tfoot():void
  {
    $tfoot = $this->xml->tfoot[0];
    $tfootStyle = $this->style($tfoot, [
      'bg-color' => $this->defaults['bgTfoot'],
      'font' => 'bold',
      'border' => $this->isBorder
    ]);
    
    if (self::$debug) {
      trigger_error(__METHOD__."\n".json_encode($tfootStyle, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), E_USER_ERROR);
    }
    
    $this->posX = 1;
    
    foreach ($tfoot->tr->td as $td) {
      $colStyles = $this->colGroup['c'.$this->posX] ?? [];
      $tdType = $colStyles['type'] ?? '';
      $tdStyle = $this->style($td, $tfootStyle);
      
      $tdStyle['align'] = '' === $tdStyle['align'] ? match ($tdType) {
        'date' => Alignment::HORIZONTAL_CENTER,
        'integer', 'numeric' => Alignment::HORIZONTAL_RIGHT,
        default => $tfootStyle['align']
      } : $tdStyle['align'];
      
      $style = [
        'alignment' => [
          'horizontal' => $this->alignHorizontal(trim($tdStyle['align'] ?? ''), $tfootStyle['align']),
          'vertical' => $this->alignVertical(trim($tdStyle['valign'] ?? $tfootStyle['valign']))
        ],
        'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'color' => [
            'rgb' => $this->color(trim($td['bg-color'] ?? ''), $tfootStyle['bg-color'])
          ]
        ],
        'font' => [
          'bold' => $tfootStyle['bold'] ?? true
        ]
      ];
      
      if ('' !== $tdStyle['color']) {
        $style['font']['color'] = [
          'rgb' => $this->color($tdStyle['color'], '')
        ];
      }
      
      if ($tdStyle['italic'] ?? false) {
        $style['font']['italic'] = true;
      }
      
      if ($tdStyle['underline'] ?? false) {
        $style['font']['underline'] = true;
      }
      
      if ($tdStyle['border']) {
        $style['borders'] = [
          'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
          ]
        ];
      }
      
      $coordinate = [
        $this->posX,
        $this->posY
      ];
      
      $this->value($tdType, $coordinate, trim($td));
      $this->setStyle($coordinate, $style);
      
      $this->posX++;
    }
  }
  
  private function thead():void
  {
    $thead = $this->xml->thead[0];
    $theadStyle = $this->style($thead, [
      'align' => Alignment::HORIZONTAL_CENTER,
      'bg-color' => $this->defaults['bgThead'],
      'font' => 'bold',
      'border' => $this->isBorder
    ]);
    
    if (self::$debug) {
      trigger_error(__METHOD__."\n".json_encode($theadStyle, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), E_USER_ERROR);
    }
    
    foreach ($thead->tr[0]->th as $th) {
      $this->sheet->setCellValueExplicit([
        $this->posX,
        $this->posY
      ], trim($th), DataType::TYPE_STRING);
      
      $style = [
        'alignment' => [
          'horizontal' => $this->alignHorizontal(trim($th['align'] ?? ''), $theadStyle['align']),
          'vertical' => $this->alignVertical(trim($th['valign'] ?? $theadStyle['valign']))
        ],
        'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'color' => [
            'rgb' => $this->color(trim($th['bg-color'] ?? ''), $theadStyle['bg-color'])
          ]
        ],
        'font' => [
          'bold' => $theadStyle['bold'] ?? true,
          'italic' => $theadStyle['italic'] ?? false,
          'color' => [
            'rgb' => $this->color(trim($th['color'] ?? ''), $theadStyle['color'])
          ]
        ]
      ];
      
      if ($theadStyle['border']) {
        $style['borders'] = [
          'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
          ]
        ];
      }
      
      $this->setStyle([
        $this->posX,
        $this->posY
      ], $style);
      
      $this->posX++;
    }
    
    # Висота шапки
    if (0 < $theadStyle['height']) {
      $this->spreadsheet->getActiveSheet()
                        ->getRowDimension($this->posY)
                        ->setRowHeight($theadStyle['height']);
    }
    
    unset($thead, $theadStyle);
  }
  
  private function type(string $type):string
  {
    
    return match (Str::lowerCase($type)) {
      'date', 'integer', 'numeric' => Str::lowerCase($type),
      default => 'char'
    };
  }
  
  private function value(string $type, array $coordinate, string $value):void
  {
    if ('' === $value) {
      
      return;
    }
    
    if ('date' === $type) {
      $date = '';
      
      if (Date::validateDate($value)) {
        $date = "$value 00:00:00";
      } elseif (Date::validateDate($value, DateEn::DATETIME)) {
        $date = $value;
      } elseif (Date::validateDate($value, DateEn::TIMESTAMP)) {
        $date = "$value:00";
      }
      
      if ('' !== $date) {
        $this->sheet->setCellValue($coordinate,
          \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(\DateTime::createFromFormat('Y-m-d H:i:s', $date)))
                    ->getStyle($coordinate)
                    ->getNumberFormat()
                    ->setFormatCode('dd.mm.yyyy');
        
        return;
      }
    }
    
    if ('numeric' === $type && (is_numeric($value) || is_float($value * 1))) {
      $this->sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_NUMERIC)
                  ->getStyle($coordinate)
                  ->getNumberFormat()
                  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
      
      return;
    }
    
    if ('integer' === $type && is_numeric($value) && !is_float($value * 1)) {
      $this->sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_NUMERIC)
                  ->getStyle($coordinate)
                  ->getNumberFormat()
                  ->setFormatCode('#,##0');
      
      return;
    }
    
    $this->sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
  }
}
<?php

namespace okushnirov\core\Library\Enums;

enum Extensions: string
{
  case DOC = '.doc';
  
  case DOCX = '.docx';
  
  case PDF = '.pdf';
  
  case XLS = '.xls';
  
  case XLSX = '.xlsx';
  
  public function getContentType():string
  {
    
    return match ($this) {
      Extensions::DOC => 'application/msword',
      Extensions::DOCX => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      Extensions::PDF => 'application/pdf',
      Extensions::XLS => 'application/vnd.ms-excel',
      Extensions::XLSX => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    };
  }
}
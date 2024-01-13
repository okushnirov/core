<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\DateFormat;

enum DateEn: string implements DateFormat
{
  case ISO = 'Y-m-d';
  
  case DATETIME = 'Y-m-d H:i:s';
  
  case TIMESTAMP = 'Y-m-d H:i';
}
<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\DateFormat;

enum DateRu: string implements DateFormat
{
  case ISO = 'd.m.Y';
  
  case DATETIME = 'd.m.Y H:i:s';
  
  case TIMESTAMP = 'd.m.Y H:i';
}
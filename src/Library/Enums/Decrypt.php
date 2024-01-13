<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\CryptType;

enum Decrypt implements CryptType
{
  case BASE;
  
  case CHR;
  
  case INT;
}
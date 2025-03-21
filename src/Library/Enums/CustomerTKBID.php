<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\CustomerTypes;

enum CustomerTKBID: int implements CustomerTypes
{
  case Company = 2;
  
  case Businessman = 3;
  
  case Person = 1;
}

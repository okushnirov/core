<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\CustomerTypes;

enum CustomerFrontID: int implements CustomerTypes
{
  case Company = 3;
  
  case Businessman = 4;
  
  case Person = 5;
}

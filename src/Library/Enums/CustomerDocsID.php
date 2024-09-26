<?php

namespace okushnirov\core\Library\Enums;

use okushnirov\core\Library\Interfaces\CustomerTypes;

enum CustomerDocsID: int implements CustomerTypes
{
  case Company = 429;
  
  case Businessman = 431;
  
  case Person = 430;
}

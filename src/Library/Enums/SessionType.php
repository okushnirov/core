<?php

namespace okushnirov\core\Library\Enums;

enum SessionType
{
  case NONE;
  
  /**
   * @deprecated
   */
  case DB;
  
  case PHP;
  
  case WS;
}
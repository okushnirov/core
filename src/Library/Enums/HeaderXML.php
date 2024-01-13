<?php

namespace okushnirov\core\Library\Enums;

enum HeaderXML: string
{
  case WINDOWS = '<?xml version="1.0" encoding="windows-1251"?>';
  
  case UTF = '<?xml version="1.0" encoding="utf-8"?>';
}
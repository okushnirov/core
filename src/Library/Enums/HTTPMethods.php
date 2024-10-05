<?php

namespace okushnirov\core\Library\Enums;

enum HTTPMethods: string
{
  case CONNECT = 'CONNECT';
  
  case DELETE = 'DELETE';
  
  case GET = 'GET';
  
  case HEAD = 'HEAD';
  
  case OPTIONS = 'OPTIONS';
  
  case PATCH = 'PATCH';
  
  case POST = 'POST';
  
  case PUT = 'PUT';
  
  case TRACE = 'TRACE';
}

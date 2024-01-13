<?php

namespace okushnirov\core\Handlers\Interfaces;

interface Handler
{
  public function __construct(array $JSON = []);
  
  public static function index():void;
}